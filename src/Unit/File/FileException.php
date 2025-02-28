<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\File;

use Flytachi\Kernel\Src\Factory\Error\ExtraException;
use Psr\Log\LogLevel;

class FileException extends ExtraException
{
    protected string $logLevel = LogLevel::CRITICAL;
}
