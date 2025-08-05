<?php

declare(strict_types=1);

namespace Flytachi\Kernel;

interface ActuatorItemInterface
{
    public function run(): void;
}
