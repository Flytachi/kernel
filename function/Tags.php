<?php

declare(strict_types=1);

if (!function_exists('resourceImport')) {
    function resourceImport(string $resourceName): void
    {
        \Flytachi\Kernel\Src\Factory\Resource::import($resourceName);
    }
}

if (!function_exists('resourceIsActiveLink')) {
    function resourceIsActiveLink(
        string $link,
        string $classNameSuccess = 'active',
        string $classNameNone = ''
    ): string {
        return \Flytachi\Kernel\Src\Factory\Resource::
            isActiveLink($link, $classNameSuccess, $classNameNone);
    }
}

if (!function_exists('resourceContent')) {
    function resourceContent(): void
    {
        \Flytachi\Kernel\Src\Factory\Resource::content();
    }
}

if (!function_exists('resourceData')) {
    function resourceData(?string $valueKey = null): mixed
    {
        return \Flytachi\Kernel\Src\Factory\Resource::getData($valueKey);
    }
}
