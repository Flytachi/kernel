<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http\Response;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Factory\Http\HttpCode;

class ResponseException extends ExtraException
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;
}
