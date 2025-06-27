<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DataTableNet;

use Flytachi\Kernel\Src\Stereotype\RequestObject;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetColumns;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetOrder;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetSearch;

class DataTableNetRequest extends RequestObject
{
    public DTNetSearch $search;
    public DTNetOrder $order;
    public DTNetColumns $columns;

    public function __construct(
        public int $draw = 1,
        public int $start = 0,
        public int $length = 10,
        ?array $search = null,
        ?array $order = null,
        ?array $columns = null
    ) {
        $this->valid('draw', '\Flytachi\Kernel\Src\Unit\Tool::isIntPositive');
        $this->valid('limit', '\Flytachi\Kernel\Src\Unit\Tool::isIntPositive');
        $this->valid('page', '\Flytachi\Kernel\Src\Unit\Tool::isIntPositive');

        // search
        $this->search = match (true) {
            $search === null => new DTNetSearch(),
            default => new DTNetSearch($search['value'] ?? '', $search['regex'] ?? false),
        };

        // order
        $this->order = new DTNetOrder($order ?? []);

        // columns
        $this->columns = new DTNetColumns($columns ?? []);
    }
}
