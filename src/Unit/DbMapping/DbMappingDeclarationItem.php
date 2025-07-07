<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DbMapping;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\DbConfigInterface;

final class DbMappingDeclarationItem
{
    private array $tables = [];
    public function __construct(public readonly DbConfigInterface $config)
    {
    }

    public function push(Table $newTable): void
    {
        $this->tables[] = $newTable;
    }

    public function getTables(): array
    {
        return $this->tables;
    }
}