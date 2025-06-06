<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Conductors;

interface Conductor
{
    public function recordAdd(string $className, int $pid): void;
    public function recordRemove(string $className, int $pid): void;
}
