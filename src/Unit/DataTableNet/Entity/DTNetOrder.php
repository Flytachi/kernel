<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DataTableNet\Entity;

class DTNetOrder
{
    /* @var array<string, 'asc'|'desc'> $items */
    public array $items;

    public function __construct(array $items = [])
    {
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->items[$key] = $value;
            }
        }
    }
}
