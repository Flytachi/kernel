<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\CDO;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class CDOException extends ExtraException
{
    protected string $logLevel = LogLevel::CRITICAL;
}
