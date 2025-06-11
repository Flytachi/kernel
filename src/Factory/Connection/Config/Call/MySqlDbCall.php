<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Call;

use Flytachi\Kernel\Src\Factory\Connection\Config\Common\BaseDbConfig;

final class MySqlDbCall extends BaseDbConfig
{
    public function __construct(
        public string $host = 'localhost',
        public int $port = 3306,
        public string $database = '',
        public string $username = 'root',
        public string $password = '',
        public ?string $charset = null,
    ) {
    }

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

    public function sepUp(): void
    {
    }
}
