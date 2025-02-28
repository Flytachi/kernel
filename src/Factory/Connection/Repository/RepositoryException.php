<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Repository;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class RepositoryException extends ExtraException
{
    protected string $logLevel = LogLevel::CRITICAL;
}
