<?php

namespace Flytachi\Kernel\Src\Errors;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class Error extends ExtraException
{
    protected $code = HttpCode::UNKNOWN_ERROR;
    protected string $logLevel = LogLevel::ERROR;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $httpCode = HttpCode::tryFrom($code);
        if ($httpCode == null) $this->logLevel = LogLevel::ALERT;
        else {
            if ($httpCode->isServerError()) {
                $this->logLevel = LogLevel::ERROR;
            } elseif ($httpCode->isClientError()) {
                $this->logLevel = LogLevel::WARNING;
            } else {
                $this->logLevel = LogLevel::NOTICE;
            }
        }
        parent::__construct($message, $code, $previous);
    }
}