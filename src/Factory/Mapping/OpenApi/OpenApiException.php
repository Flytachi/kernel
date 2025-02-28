<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping\OpenApi;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class OpenApiException extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}
