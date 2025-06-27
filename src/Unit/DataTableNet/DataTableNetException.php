<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DataTableNet;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Flytachi\Kernel\Src\Http\HttpCode;
use Psr\Log\LogLevel;

class DataTableNetException extends ExtraException
{
    protected string $logLevel = LogLevel::ERROR;
}
