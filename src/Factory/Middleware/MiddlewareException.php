<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Middleware;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Factory\Http\HttpCode;

class MiddlewareException extends ExtraException
{
    protected $code = HttpCode::UNAUTHORIZED->value;
}
