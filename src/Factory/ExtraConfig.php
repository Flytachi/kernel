<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory;

abstract class ExtraConfig
{
    public static string $pathRoot;
    public static string $pathEnv;
    public static string $pathApp;
    public static string $pathPublic;
    public static string $pathResource;
    public static string $pathStorage;
    public static string $pathStorageCache;
    public static string $pathStorageLog;
    public static string $pathFileMapping;

    /**
     * @param string|null $pathRoot
     * @param string|null $pathApp
     * @param string|null $pathEnv
     * @param string|null $pathPublic
     * @param string|null $pathResource
     * @param string|null $pathStorage
     * @param string|null $pathStorageCache
     * @param string|null $pathStorageLog
     * @param string|null $pathFileMapping
     * @return void
     */
    public static function init(
        ?string $pathRoot = null,
        ?string $pathApp = null,
        ?string $pathEnv = null,
        ?string $pathPublic = null,
        ?string $pathResource = null,
        ?string $pathStorage = null,
        ?string $pathStorageCache = null,
        ?string $pathStorageLog = null,
        ?string $pathFileMapping = null
    ): void {
        // root
        if ($pathRoot === null) {
            $pathRoot = dirname(__DIR__, 5);
        }

        // app
        if ($pathApp === null) {
            $pathApp = $pathRoot . '/app';
        }

        // env
        if ($pathEnv === null) {
            $pathEnv = $pathRoot . '/.env';
        }

        // public
        if ($pathPublic === null) {
            $pathPublic = $pathRoot . '/public';
        }

        // resource
        if ($pathStorageLog === null) {
            $pathResource = $pathRoot . '/resources';
        }

        // storage
        if ($pathStorage === null) {
            $pathStorage = $pathRoot . '/storage';
        }

        // storage cache
        if ($pathStorageCache === null) {
            $pathStorageCache = $pathStorage . '/cache';
        }

        // storage log
        if ($pathStorageLog === null) {
            $pathStorageLog = $pathStorage . '/logs';
        }

        // mapping
        if ($pathFileMapping === null) {
            $pathFileMapping = $pathStorageCache . '/mapping.php';
        }

        self::$pathRoot = $pathRoot;
        self::$pathApp = $pathApp;
        self::$pathEnv = $pathEnv;
        self::$pathPublic = $pathPublic;
        self::$pathResource = $pathResource;
        self::$pathStorage = $pathStorage;
        self::$pathStorageCache = $pathStorageCache;
        self::$pathStorageLog = $pathStorageLog;
        self::$pathFileMapping = $pathFileMapping;
    }
}
