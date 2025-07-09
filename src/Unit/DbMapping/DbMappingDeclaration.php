<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DbMapping;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\DbConfigInterface;

final class DbMappingDeclaration
{
    /** @var DbMappingDeclarationItem[] */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function push(DbConfigInterface $config, Table $structureTable): void
    {
        foreach ($this->items as $item) {
            if ($item->config::class === $config::class) {
                $item->push($structureTable);
                return;
            }
        }
        $newItem = new DbMappingDeclarationItem($config);
        $newItem->push($structureTable);
        $this->items[] = $newItem;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
