<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\RCartridge;

interface RouteCartridgeInterface
{
    public function wrapInput(): array;
    public function wrapOutput(mixed $result): mixed;
}
