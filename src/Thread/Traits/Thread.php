<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Traits;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Thread\ThreadException;

trait Thread
{
    /**
     * Executes a function in a separate child process using forking.
     *
     * @param callable $function The function to be executed in the child process.
     * @return int The process ID of the child process.
     */
    final protected function thread(callable $function): int
    {
        try {
            $pid = pcntl_fork();
            if ($pid != -1) {
                if ($pid == 0) {
                    // Child process
                    try {
                        $this->pid = getmypid();
                        $this->threadStartRun();
                        try {
                            $function();
                        } catch (\Throwable $exception) {
                            $this->logger?->error(
                                "[$this->pid] Thread Logic => " . $exception->getMessage()
                                . "\n" . $exception->getTraceAsString()
                            );
                        }
                    } catch (\Throwable $exception) {
                        $this->logger?->error(
                            "[$this->pid] Thread: " . $exception->getMessage()
                            . "\n" . $exception->getTraceAsString()
                        );
                    } finally {
                        $this->threadEndRun();
                        exit(0);
                    }
                } else {
                    // Parent process
                    if ($this->childrenPidSave) {
                        $this->childrenPid[] = $pid;
                    }
                    return $pid;
                }
            } else {
                ThreadException::throw("[{$this->pid}] Unable to fork process.");
            }
        } catch (\Throwable $e) {
            $this->logger?->critical($e->getMessage() . "\n" . $e->getTraceAsString());
            return 0;
        }
    }

    /**
     * Executes the thread process by forking a new process and running the proc method.
     *
     * @param mixed $data The data to be passed to the proc method. Default is null.
     * @return int The PID (Process ID) of the child process if the process was successfully forked, otherwise null.
     */
    final protected function threadProc(mixed $data = null): int
    {
        try {
            $pid = pcntl_fork();
            if ($pid != -1) {
                if ($pid == 0) {
                    // Child process
                    try {
                        $this->pid = getmypid();
                        $this->threadStartRun();
                        try {
                            $this->proc($data);
                        } catch (\Throwable $exception) {
                            $this->logger?->error(
                                "[$this->pid] Thread(proc) Logic => " . $exception->getMessage()
                                . "\n" . $exception->getTraceAsString()
                            );
                        }
                    } catch (\Throwable $exception) {
                        $this->logger?->error(
                            "[$this->pid] Thread(proc): " . $exception->getMessage()
                            . "\n" . $exception->getTraceAsString()
                        );
                    } finally {
                        $this->threadEndRun();
                        exit(0);
                    }
                } else {
                    // Parent process
                    if ($this->childrenPidSave) {
                        $this->childrenPid[] = $pid;
                    }
                    return $pid;
                }
            } else {
                ThreadException::throw("[{$this->pid}] Unable to fork process.");
            }
        } catch (\Throwable $e) {
            $this->logger?->critical($e->getMessage() . "\n" . $e->getTraceAsString());
            return 0;
        }
    }

    protected function threadStartRun(): void
    {
        $this->iAmChild = true;
        $this->logger = Extra::$logger->withName("[{$this->pid}] " . static::class);
        if (PHP_SAPI === 'cli') {
            cli_set_process_title('extra thread ' . static::class);
        }
    }

    protected function threadEndRun(): void
    {
    }

    public function proc(mixed $data = null): void
    {
        $this->logger?->info("-proc- running");
    }
}
