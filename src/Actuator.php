<?php

namespace Flytachi\Kernel\Src;

use Flytachi\Kernel\Src\Http\Router;

final class Actuator
{
    public static function use(): never
    {
        Router::run(env('DEBUG', false));
        exit();
    }
}
