<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Common;

use Flytachi\Kernel\Src\Factory\Connection\CDO\CDO;
use Flytachi\Kernel\Src\Factory\Connection\ConnectionPool;

trait EntityCallDbTrait
{
    /**
     * @return CDO
     */
    final public static function entity(): CDO
    {
        return ConnectionPool::db(static::class);
    }
}
