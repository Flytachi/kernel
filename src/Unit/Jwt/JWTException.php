<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\Jwt;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class JWTException extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}
