<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Log;

use Flytachi\Kernel\Extra;
use Psr\Log\AbstractLogger;

final class Log extends AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        Extra::$logger
            ->withName("LOG")
            ?->log($level, $message, $context);
    }
}
