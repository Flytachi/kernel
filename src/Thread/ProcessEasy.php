<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Thread\Conductors\Conductor;
use Flytachi\Kernel\Src\Thread\Conductors\ConductorEmpty;
use Flytachi\Kernel\Src\Thread\Dispatcher\Dispatcher;
use Flytachi\Kernel\Src\Thread\Dispatcher\DispatcherInterface;
use Flytachi\Kernel\Src\Thread\Traits\ProcessEasyHandler;

abstract class ProcessEasy extends Dispatcher implements DispatcherInterface
{
    use ProcessEasyHandler;

    protected string $conductorClassName = ConductorEmpty::class;
    private Conductor $conductor;
    /** @var int $pid System process id */
    protected int $pid;

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
            cli_set_process_title('extra job ' . static::class);
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
        if (PHP_SAPI === 'cli') {
            $this->conductor->recordRemove(static::class, $this->pid);
        }
    }

    /**
     * @throws ThreadException
     */
    final public static function dispatch(mixed $data = null): int
    {
        return self::runnable($data);
    }
}
