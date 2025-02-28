<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Thread\Conductors\Conductor;
use Flytachi\Kernel\Src\Thread\Conductors\ConductorClusterJson;
use Flytachi\Kernel\Src\Thread\Dispatcher\Dispatcher;
use Flytachi\Kernel\Src\Thread\Dispatcher\DispatcherInterface;
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

    protected string $conductorClassName = ConductorClusterJson::class;
    private Conductor $conductor;
    /** @var int $pid System process id */
    protected int $pid;
    private bool $iAmChild = false;
    protected int $balancer = 10;

    final public static function stmPath(): string
    {
        return Extra::$pathStorageCache . '/' . hash('xxh64', static::class) . '__c.json';
    }

    final public static function stmThreadsPath(): string
    {
        return Extra::$pathStorageCache . '/' . hash('xxh64', static::class) . '__t';
    }

    protected final function streaming(callable $complianceCallable, ?callable $negationCallable = null): void
    {
        while (true) {
            if ($this->threadCount() < $this->balancer) {
                $complianceCallable();
            } else {
                if ($negationCallable !== null) $negationCallable();
            }
            usleep( (int) ($this->balancer < 1000 ? ceil(1_000_000 / $this->balancer) : 1000) );
        }
    }

    public static function start(mixed $data = null): int
    {
        $process = new static();

        try {
            $process->conductor = new $process->conductorClassName();
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
        if (!is_dir(static::stmThreadsPath())) mkdir(static::stmThreadsPath(), recursive: true);
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
            $this->conductor->recordAdd(static::class, $this->pid);
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
        if (is_dir(static::stmThreadsPath())) {
            @rmdir(static::stmThreadsPath());
        }
        if (PHP_SAPI === 'cli') {
            $this->conductor->recordRemove(static::class, $this->pid);
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
        $status = static::status();
        if ($status) {
            throw new ThreadException("Cluster process already exist [PID:{$status['pid']}] ({$status['startedAt']})",
                HttpCode::LOCKED->value);
        }
        else return self::runnable($data);
    }

    public static function status(): ?array
    {
        try {
            return file_exists(static::stmPath())
                ? JSON::read(static::stmPath()) : null;
        } catch (FileException $e) {
            return null;
        }
    }

    /**
     * @throws ThreadException
     */
    public static function stop(): bool
    {
        $status = static::status();
        if ($status) return Signal::interrupt($status['pid']);
        else {
            throw new ThreadException('Cluster process has not started', HttpCode::LOCKED->value);
        }
    }
}
