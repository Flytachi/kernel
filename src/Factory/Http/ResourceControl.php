<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http;

use Flytachi\Kernel\Extra;

abstract class ResourceControl
{
    final public static function import(string $path): void
    {
        include Extra::$pathResource . "/$path.php";
        ResourceTree::registerAdditionResource(Extra::$pathResource . "/$path.php");
    }

    final public static function content(): void
    {
        ResourceTree::importResource();
    }

    final public static function getData(?string $valueKey = null): mixed
    {
        return ResourceTree::getResourceData($valueKey);
    }
}
