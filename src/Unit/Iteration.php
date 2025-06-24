<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit;

use Closure;
use Flytachi\Kernel\Extra;

/**
 *  Extra collection
 *
 *  Iteration
 *
 *  @package Extra\Src\Sheath
 *  @version 1.0
 *  @author itachi
 */
class Iteration
{
    /**
     * @param int $maxAttempts
     * @param callable $func
     * @param int $sleepSecond
     * @param string $exceptionClass
     * @return void
     * @throws UnitException
     */
    public static function callThrow(
        int $maxAttempts,
        callable $func,
        int $sleepSecond = 0,
        string $exceptionClass = \Throwable::class
    ): void {
        $label = self::callableName($func);
        $attempts = 0;
        Extra::$logger->withName("Iteration::callThrow")->debug("Iteration Start [attempt:{$maxAttempts}] {$label}");
        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                Extra::$logger->withName("Iteration::callThrow")->debug("Calling {$attempts}");
                $func($attempts);
                return;
            } catch (\Throwable $error) {
                Extra::$logger->withName("Iteration::callThrow")->debug("Throw {$attempts} - "
                    . $error->getMessage() . PHP_EOL . $error->getTraceAsString());
                if ($error instanceof $exceptionClass) {
                    if ($attempts == $maxAttempts) {
                        throw new UnitException($error->getMessage(), $error->getCode(), $error);
                    }
                    if ($sleepSecond != 0) {
                        TimeTool::sleepSec($sleepSecond);
                    }
                } else {
                    throw new UnitException($error->getMessage(), $error->getCode(), $error);
                }
            }
        }
    }

    public static function callableName(callable $callable): string
    {
        return match (true) {
            is_string($callable) && strpos($callable, '::') => '[static] ' . $callable,
            is_string($callable) => '[function] ' . $callable,
            is_array($callable) && is_object($callable[0])
                => '[method] ' . get_class($callable[0])  . '->' . $callable[1],
            is_array($callable) => '[static] ' . $callable[0]  . '::' . $callable[1],
            $callable instanceof Closure => '[closure]',
            is_object($callable) => '[invokable] ' . get_class((object) $callable),
            default => '[unknown]'
        };
    }
}
