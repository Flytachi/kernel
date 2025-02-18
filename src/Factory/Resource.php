<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory;

use Flytachi\Kernel\Src\Http\ResourceControl;

abstract class Resource extends ResourceControl
{
    public static function isActiveLink(array|string $link, string $classNameSuccess = 'active', string $classNameNone = ''): string
    {
        if (is_array($link)) {
            if (in_array($_SERVER['REQUEST_URI'], $link)) return $classNameSuccess;
        } else {
            if($_SERVER['REQUEST_URI'] == $link) return $classNameSuccess;
        }
        return $classNameNone;
    }
}
