<?php

namespace Flytachi\Kernel\Src;

final class Actuator
{
    public static function use(ActuatorItemInterface ...$items): never
    {
        foreach ($items as $item) {
            $item->run();
        }
        exit;
    }
}
