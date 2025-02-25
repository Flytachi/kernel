<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Error;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Http\HttpCode;

abstract class ExtraException extends \Exception
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        if ($code == 0) $code = $this->code;
        parent::__construct($message, $code, $previous);
        Extra::$logger->debug(sprintf(
            "Exception: %s\nStack trace:\n%s",
            $this->getMessage(),
            $this->getTraceAsString(),
        ));
    }

    public static function throw(HttpCode $httpCode, string $message, ?\Throwable $previous = null)
    {
        throw new static($message, $httpCode->value, $previous);
    }
}
