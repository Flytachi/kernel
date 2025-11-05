<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Thread\Dispatcher\Dispatcher;
use Flytachi\Kernel\Src\Thread\Dispatcher\DispatcherInterface;
use Flytachi\Kernel\Src\Thread\Entity\CInfo;
use Flytachi\Kernel\Src\Thread\Entity\Condition;
use Flytachi\Kernel\Src\Thread\Entity\CStatus;
use Flytachi\Kernel\Src\Thread\Entity\ProcessStats;
use Flytachi\Kernel\Src\Thread\Traits\ClusterHandler;
use Flytachi\Kernel\Src\Thread\Traits\ClusterStatement;
use Flytachi\Kernel\Src\Thread\Traits\ClusterThread;
use Flytachi\Kernel\Src\Unit\File\FileException;
use Flytachi\Kernel\Src\Unit\File\JSON;

abstract class ProcessCluster extends Dispatcher implements DispatcherInterface
{
    use ClusterStatement;
    use ClusterThread;
    use ClusterHandler;

    /** @var int $pid System process id */
    protected int $pid;
    protected static string $EC_MAIN = 'clusters';
    protected static string $EC_THREADS = 'clusters-threads';
    private bool $iAmChild = false;
    protected int $balancer = 0;

    final public static function stmName(): string
    {
        return hash('xxh64', static::class);
    }

    final protected function streaming(callable $complianceCallable, ?callable $negationCallable = null): void
    {
        while (true) {
            if (static::threadQty() < $this->balancer) {
                $complianceCallable();
            } else {
                if ($negationCallable !== null) {
                    $negationCallable();
                }
            }
            usleep((int) ($this->balancer < 1000 ? ceil(1_000_000 / $this->balancer) : 1000));
        }
    }

    public static function start(mixed $data = null): int
    {
        $process = new static();

        try {
            $process->startRun();
            $process->run($data);
        } catch (\Throwable $e) {
            $process->logger?->critical($e->getMessage());
        } finally {
            $process->endRun();
        }
        return $process->pid;
    }

    /**
     * Starts the run process.
     *
     * This method sets the current process ID, registers signal handlers for SIGHUP, SIGINT, and SIGTERM,
     * sets the process title for CLI, and adds the current class to the conductor's record.
     *
     * @return void
     */
    private function startRun(): void
    {
        $this->pid = getmypid();
        $this->logger = Extra::$logger->withName("[{$this->pid}] " . static::class);

        if (PHP_SAPI === 'cli') {
            pcntl_signal(SIGHUP, function () {
                $this->signClose();
            });
            pcntl_signal(SIGINT, function () {
                $this->signInterrupt();
            });
            pcntl_signal(SIGTERM, function () {
                $this->signTermination();
            });
            cli_set_process_title('extra process ' . static::class);
            Extra::store(static::$EC_MAIN)->write(static::stmName(), new CStatus(
                pid: $this->pid,
                className: static::class,
                condition: Condition::STARTED,
                startedAt: time(),
                balancer: $this->balancer,
                info: []
            ));
        }
    }

    /**
     * Ends the execution of the run method.
     *
     * This method is responsible for performing any necessary clean-up tasks
     * after the run method finishes executing. If the PHP SAPI (Server Application
     * Programming Interface) is 'cli' (Command Line Interface), it records the
     * removal of the class and its process ID ($pid) to the conductor.
     *
     * @return void
     */
    private function endRun(): void
    {
        if (PHP_SAPI === 'cli') {
            Extra::store(static::$EC_MAIN)->del(static::stmName());
        }
    }

    final public function wait(int $pid, ?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            pcntl_waitpid($pid, $status);
            if (!is_null($callableEndChild)) {
                $callableEndChild($pid, $status);
            }
        }
    }

    /**
     * Waits for child processes to finish execution.
     *
     * @param callable|null $callableEndChild Optional. A callback function that will be called
     * with the child process ID and status after it finishes execution. Default is null.
     * @return void
     */
    final public function waitAll(?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            foreach (static::threadList() as $threadPid) {
                pcntl_waitpid($threadPid, $status);
                if (!is_null($callableEndChild)) {
                    $callableEndChild($threadPid, $status);
                }
            }
        }
    }

    /**
     * @throws ThreadException
     */
    final public static function dispatch(mixed $data = null): int
    {
        $info = static::status();
        if ($info) {
            throw new ThreadException(
                "Cluster process already exist [PID:{$info->status->pid}] ({$info->status->getStartedAt()})",
                HttpCode::LOCKED->value
            );
        } else {
            return self::runnable($data);
        }
    }

    public static function status(bool $showStats = false): ?CInfo
    {
        try {
            /** @var ?CStatus $status */
            $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
            if (!$status) {
                return null;
            }

            if (!posix_getpgid($status->pid)) {
                Extra::store(static::$EC_MAIN)->del(static::stmName());
                return null;
            }

            return new CInfo(
                status: $status,
                stats: $showStats ? ProcessStats::ofPid($status->pid) : null
            );
        } catch (FileException $e) {
            return null;
        }
    }

    /**
     * @throws ThreadException
     */
    public static function stop(): bool
    {
        $info = static::status();
        if ($info) {
            return Signal::interrupt($info->status->pid);
        } else {
            throw new ThreadException('Cluster process has not started', HttpCode::LOCKED->value);
        }
    }
}
