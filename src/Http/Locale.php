<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Unit\Tool;

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

    public static function setPath(string $path): void
    {
        self::init();
        self::$locale->path = trim($path, '/');
    }

    public static function setLang(string $lang): void
    {
        self::init();
        self::$locale->lang = trim($lang, '/');
    }

    public static function trans(string $key, ?array $params = null): string
    {
        self::init();
        // include
        if (!isset($GLOBALS['DICTIONARY'])) {
            $path = self::$locale->path . '/'. self::$locale->lang .'.php';
            $GLOBALS['DICTIONARY'] = file_exists($path) ? include $path : [];
        }
        // return
        $value = Tool::arrayNestedValue($GLOBALS['DICTIONARY'], explode('.', $key));

        if (is_string($value)) {
            return empty($params) ? $value : sprintf($value, ...$params);
        } else return $key;
    }
}