<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\Blink;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class BlinkException extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}
