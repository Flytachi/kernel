<?php

declare(strict_types=1);

namespace Flytachi\Kernel;

use Dotenv\Dotenv;
use Flytachi\Kernel\Src\Factory\ExtraConfig;
use Flytachi\Kernel\Src\Log\ExtraLogger;
use Psr\Log\LoggerInterface;

/**
 * Class Extra
 *
 * @version 1.3
 * @author Flytachi
 */
final class Extra extends ExtraConfig
{
    public static LoggerInterface $logger;

    public static function init(
        ?string $pathRoot = null,
        ?string $pathMain = null,
        ?string $pathEnv = null,
        ?string $pathPublic = null,
        ?string $pathResource = null,
        ?string $pathStorage = null,
        ?string $pathStorageCache = null,
        ?string $pathStorageLog = null,
        ?string $pathFileMapping = null,
        ?LoggerInterface $logger = null
    ): void {
        defined('EXTRA_STARTUP_TIME') or define('EXTRA_STARTUP_TIME', microtime(true));
        parent::init(
            $pathRoot,
            $pathMain,
            $pathEnv,
            $pathPublic,
            $pathResource,
            $pathStorage,
            $pathStorageCache,
            $pathStorageLog,
            $pathFileMapping
        );

        Dotenv::createImmutable(self::$pathRoot)
            ->safeLoad();

        defined('SERVER_SCHEME') or define('SERVER_SCHEME', (
                $_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            ini_set('error_reporting', 0);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }

        // logger
        if ($logger === null) {
            self::$logger = new ExtraLogger('Extra');
        } else {
            self::$logger = $logger;
        }
    }

    public static function info(): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/composer.json') ?: '',
            true
        ) ?? [];
    }

    public static function projectInfo(): ?array
    {
        if (isset(self::$pathRoot)) {
            return json_decode(
                file_get_contents(self::$pathRoot . '/composer.json') ?: '',
                true
            ) ?? [];
        } else {
            return null;
        }
    }
}
