<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Middleware;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class MiddlewareException extends ExtraException
{
    protected $code = HttpCode::UNAUTHORIZED->value;
    protected string $logLevel = LogLevel::WARNING;
}
