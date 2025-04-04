<?php

namespace Flytachi\Kernel\Src\Factory\Error;

use Flytachi\Kernel\Src\Http\HttpCode;

trait ExtraExceptionTrait
{
    /**
     * @throws self
     */
    public static function throw(string $message, ?HttpCode $httpCode = null, ?\Throwable $previous = null)
    {
        throw new static($message, $httpCode?->value ?: 0, $previous);
    }
}
