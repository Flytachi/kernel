<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class ThreadException extends ExtraException
{
    protected string $logLevel = LogLevel::CRITICAL;
}
