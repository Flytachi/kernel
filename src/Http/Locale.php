<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Extra;

final class Locale
{
    private static ?Locale $locale = null;
    private string $path;
    private string $lang;

    final public function __construct(
        string $path,
        string $lang = 'en'
    ) {
        $this->path = $path;
        $this->lang = $lang;
    }

    private static function init(): void
    {
        if (self::$locale === null) {
            self::$locale = new Locale(
                Extra::$pathRoot . '/lang',
                Header::getHeader('Accept-Language') ?: 'en'
            );
        }
    }

    private static function setPath(string $path): void
    {
        self::init();
        self::$locale->path = trim($path, '/');
    }

    private static function setLang(string $lang): void
    {
        self::init();
        self::$locale->lang = trim($lang, '/');
    }

    public static function trans(string $key, ?array $params = null): string
    {
        self::init();
        if (!isset($GLOBALS['DICTIONARY'])) {
            $path = self::$locale->path . '/'. self::$locale->lang .'.php';
            $GLOBALS['DICTIONARY'] = file_exists($path) ? include $path : [];
        }
        if (isset($GLOBALS['DICTIONARY'][$key]) && $GLOBALS['DICTIONARY'][$key])
            return empty($params)
                ? $GLOBALS['DICTIONARY'][$key]
                : sprintf($GLOBALS['DICTIONARY'][$key], ...$params);
        else return $key;
    }
}