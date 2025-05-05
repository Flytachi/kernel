<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Call;

use Flytachi\Kernel\Src\Factory\Connection\Config\Common\BaseRedisConfig;

final class RedisCall extends BaseRedisConfig
{
    public function __construct(
        public string $host = 'localhost',
        public $port = 6379,
        public $password = '',
        public $databaseIndex = 0
    ) {
    }

    final public function sepUp(): void
    {
    }
}
