<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Dispatcher;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Thread\ThreadException;
use Psr\Log\LoggerInterface;

/**
 * Class Dispatcher
 *
 * `Dispatcher` is an abstract class that provides a method to run a process in the background.
 * The class to execute and data to pass to the run process can be specified.
 *
 * The methods provided by `Dispatcher` include:
 *
 * - `runnable(mixed $data = null): int`: Executes a new process by the class from which this method is called.
 *   It takes data as a parameter if any, and returns the process ID of the created process.
 *
 * @version 4.0
 * @author Flytachi
 */
abstract class Dispatcher
{
    const string ES_NAME = 'threads/dispatcher';
    protected ?LoggerInterface $logger = null;

    public function __construct()
    {
        set_time_limit(0);
        ob_implicit_flush();
    }

    /**
     * Runs a process in the background by executing a command with given class name and data.
     *
     * @param mixed $data The data to be passed to the process. Default is null.
     * @return int The process ID of the spawned process.
     * @throws ThreadException
     */
    final protected static function runnable(mixed $data = null): int
    {
        try {
            if ($data) {
                $fileName = uniqid('cache-');
                Extra::store(Dispatcher::ES_NAME)->write($fileName, $data);
            }

            $selfDirectory = getcwd();
            chdir(Extra::$pathRoot);
            $pid = (int) exec(sprintf(
                "php extra run thread --name='%s' %s > %s 2>&1 & echo $!",
                static::class,
                ($data ? "--cache='{$fileName}'" : ''),
                "/dev/null"
            ));
            chdir($selfDirectory);
            return $pid;
        } catch (\Throwable $err) {
            throw new ThreadException($err->getMessage(), previous: $err);
        }
    }
}
