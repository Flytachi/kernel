<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DataTableNet;

use Flytachi\Kernel\Src\Factory\Connection\Qb;
use Flytachi\Kernel\Src\Stereotype\RequestObject;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetColumn;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetColumns;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetOrder;
use Flytachi\Kernel\Src\Unit\DataTableNet\Entity\DTNetSearch;

class DataTableNetRequest extends RequestObject
{
    public DTNetSearch $search;
    /* @var DTNetOrder[] $order */
    public array $order = [];
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
        $this->valid('start', 'is_numeric');
        $this->valid('length', '\Flytachi\Kernel\Src\Unit\Tool::isIntPositive');

        // search
        $this->search = match (true) {
            $search === null => new DTNetSearch(),
            default => new DTNetSearch($search['value'] ?? '', $search['regex'] ?? false),
        };

        // order
        if (!empty($order)) {
            $this->order = [];
            foreach ($order as $orderItem) {
                $this->order[] = new DTNetOrder($orderItem['column'], $orderItem['dir']);
            }
        }

        // columns
        $this->columns = new DTNetColumns($columns ?? []);
    }

    /**
     * Generates a comma-separated list of column names for use in a SQL SELECT clause.
     *
     * If a `$resetNames` map is provided, it will override the `name` property
     * of matching columns based on their `data` keys.
     *
     * @param array<string, string> $resetNames An associative array of data => name overrides
     * @return string A comma-separated list of column names for SQL selection
     */
    public function selection(array $resetNames = []): string
    {
        $naming = array_map(
            function (DTNetColumn $item) use ($resetNames) {
                if (isset($resetNames[$item->data])) {
                    $item->name = $resetNames[$item->data];
                }
                return $item->name ?: $item->data;
            },
            $this->columns->items
        );
        return implode(', ', $naming);
    }

    public function filter(): Qb
    {
        return Qb::empty();
    }

    /**
     * Builds the SQL ORDER BY clause from the column-ordering configuration.
     *
     * Iterates through the list of order instructions and generates a comma-separated
     * ORDER BY expression using each column's `name` (or `data` as fallback) and the sort direction (ASC/DESC).
     *
     * If no valid order instructions are found and a `$defaultContext` is provided, it will be returned instead.
     *
     * @param string|null $defaultContext Optional fallback string to return if no order instructions exist
     * @return string Comma-separated ORDER BY expression (without the "ORDER BY" keyword), or the fallback if defined
     */
    public function order(?string $defaultContext = null): string
    {
        $orderClauses = [];

        foreach ($this->order as $orderItem) {
            /** @var DTNetOrder $orderItem */
            $column = $this->columns->items[$orderItem->column] ?? null;
            if (!$column || !$column->orderable) {
                continue;
            }

            $field = $column->name ?: $column->data;
            $direction = strtolower($orderItem->dir) === 'desc' ? 'DESC' : 'ASC';
            $orderClauses[] = "{$field} {$direction}";
        }

        if (empty($orderClauses) && $defaultContext !== null) {
            return $defaultContext;
        }

        return implode(', ', $orderClauses);
    }
}
