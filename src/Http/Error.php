<?php

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class Error extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}