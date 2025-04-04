<?php

namespace Flytachi\Kernel\Src\Errors;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class ClientError extends ExtraException
{
    protected $code = HttpCode::CONFLICT;
    protected string $logLevel = LogLevel::WARNING;
}
