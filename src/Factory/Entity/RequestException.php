<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Entity;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class RequestException extends ExtraException
{
    protected $code = HttpCode::BAD_REQUEST->value;
    protected string $logLevel = LogLevel::WARNING;
}
