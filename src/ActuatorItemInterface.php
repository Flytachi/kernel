<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src;

interface ActuatorItemInterface
{
    public function run(): void;
}
