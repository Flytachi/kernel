<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config;

use Flytachi\Kernel\Src\Factory\Connection\Config\Common\BaseDbConfig;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\EntityCallDbTrait;

abstract class MySqlDbConfig extends BaseDbConfig
{
    use EntityCallDbTrait;

    protected string $host = 'localhost';
    protected int $port = 3306;
    protected string $database = '';
    protected string $username = 'root';
    protected string $password = '';
    protected ?string $charset = null;

    public function getDns(): string
    {
        $dns = parent::getDns();
        if ($this->charset !== null) {
            $dns .= 'charset=' . $this->charset . ';';
        }
        return $dns;
    }

    final public function getDriver(): string
    {
        return 'mysql';
    }
}
