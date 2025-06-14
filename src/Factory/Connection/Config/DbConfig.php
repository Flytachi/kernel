<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config;

use Flytachi\Kernel\Src\Factory\Connection\Config\Common\BaseDbConfig;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\EntityCallDbTrait;

abstract class DbConfig extends BaseDbConfig
{
    use EntityCallDbTrait;

    protected string $driver;
    protected string $host;
    protected int $port;
    protected string $database;
    protected string $username;
    protected string $password;

    final public function getDriver(): string
    {
        return $this->driver;
    }
}
