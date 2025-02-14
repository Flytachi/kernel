<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Error;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Factory\Http\Header;
use Flytachi\Kernel\Src\Factory\Http\HttpCode;

abstract class ExtraException extends \Exception implements ExtraThrowable
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Extra::$logger->debug(sprintf(
            "Exception: %s\nStack trace:\n%s",
            $this->getMessage(),
            $this->getTraceAsString(),
        ));
    }


    public function getHeader(): array
    {
        $accept = Header::getHeader('Accept');
        if (str_contains($accept, 'text/html')) {
            return ['Content-Type' => 'text/html; charset=utf-8'];
        } else {
            return ['Content-Type' => $accept];
        }
    }

    public function getBody(): string
    {
        return match (Header::getHeader('Accept')) {
            'application/json' => ExceptionWrapper::constructJson($this),
            'application/xml' => ExceptionWrapper::constructXml($this),
            default => ExceptionWrapper::constructDefault($this)
        };
    }
}
