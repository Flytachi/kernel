<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class RouterException extends ExtraException
{
    protected string $logLevel = LogLevel::CRITICAL;
}
