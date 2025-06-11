<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Common;

use Flytachi\Kernel\Src\Factory\Connection\ConnectionPool;
use Redis;

trait EntityCallRedisTrait
{
    /**
     * @return Redis
     */
    final public static function entity(): Redis
    {
        return ConnectionPool::store(static::class);
    }
}
