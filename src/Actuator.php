<?php

namespace Flytachi\Kernel\Src;

use Flytachi\Kernel\Src\Http\Router;

final class Actuator
{
    public static function use(ActuatorItemInterface ...$items): never
    {
        foreach ($items as $item) {
            $item->run();
        }
        exit();
    }
}
