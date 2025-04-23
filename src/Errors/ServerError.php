<?php

namespace Flytachi\Kernel\Src\Errors;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class ServerError extends ExtraException
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;
    protected string $logLevel = LogLevel::ERROR;
}
