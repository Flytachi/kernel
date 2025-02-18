<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Extra;

abstract class ResourceControl
{
    final public static function import(string $resourceName): void
    {
        include Extra::$pathResource . "/$resourceName.php";
        ResourceTree::registerAdditionResource(Extra::$pathResource . "/$resourceName.php");
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
