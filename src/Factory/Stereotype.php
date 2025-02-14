<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory;

use Flytachi\Kernel\Extra;
use Psr\Log\LoggerAwareTrait;

abstract class Stereotype
{
    use LoggerAwareTrait;

    public function __construct()
    {
        self::setLogger(Extra::$logger->withName(static::class));
    }
}
