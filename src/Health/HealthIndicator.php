<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Src\ActuatorItemInterface;

class HealthIndicator implements HealthIndicatorInterface, ActuatorItemInterface
{
    public function run(): void
    {
        // TODO: Implement run() method.
    }
}
