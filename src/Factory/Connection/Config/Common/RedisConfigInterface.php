<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Common;

use Redis;

interface RedisConfigInterface
{
    public function sepUp(): void;
    public function connect(): void;
    public function disconnect(): void;
    public function reconnect(): void;
    public function connection(): Redis;
}
