<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\Blink;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class BlinkException extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}
