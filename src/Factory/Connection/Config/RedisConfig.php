<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config;

use Flytachi\Kernel\Src\Factory\Connection\Config\Common\BaseRedisConfig;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\EntityCallRedisTrait;

abstract class RedisConfig extends BaseRedisConfig
{
    use EntityCallRedisTrait;

    protected string $host = 'localhost';
    protected int $port = 6379;
    protected string $password = '';
    protected int $databaseIndex = 0;
}
