<?php

namespace Flytachi\Kernel\Src\Factory\Error;

use Flytachi\Kernel\Src\Http\HttpCode;

trait ExtraExceptionTrait
{
    /**
     * @throws self
     */
    public static function throw(
        string $message,
        HttpCode|int|null $httpCode = null,
        ?\Throwable $previous = null
    ) {
        $code = (is_numeric($httpCode) ? (int)$httpCode : $httpCode?->value) ?: 0;
        throw new static($message, $code, $previous);
    }
}
