<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\ActuatorItemInterface;

class Health implements ActuatorItemInterface
{
    public function run(): void
    {
        dd((new HealthIndicator)->health());
    }
}
