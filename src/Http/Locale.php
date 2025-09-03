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
        self::$locale->path = rtrim($path, '/');
    }

    public static function setLang(string $lang): void
    {
        self::init();
        self::$locale->lang = trim($lang, '/');
    }

    public static function getPath(): string
    {
        self::init();
        return self::$locale->path;
    }

    public static function getLang(): string
    {
        self::init();
        return self::$locale->lang;
    }

    /**
     * Translates a given key using the loaded dictionary.
     *
     * This method retrieves the translation string from the dictionary using a dot-separated key.
     * If parameters are provided, they will be inserted into the translated string using `sprintf()`.
     * If the key is not found, it returns the key as is.
     *
     * @param string $key The translation key, using dot notation for nested values.
     * @param array|null $params Optional parameters to replace placeholders in the translation string.
     *
     * @return string The translated string or the key if no translation is found.
     *
     * @example
     * // Dictionary file (en.php):
     * return [
     *     'error' => [
     *         'not_found' => 'Page not found',
     *         'server' => 'Server error: %s',
     *     ],
     *     'user' => [
     *         'welcome' => 'Welcome, %s!',
     *     ],
     * ];
     *
     * // Usage:
     * echo Locale::translate('error.not_found');         // Output: "Page not found"
     * echo Locale::translate('error.server', ['500']);  // Output: "Server error: 500"
     * echo Locale::translate('user.welcome', ['John']); // Output: "Welcome, John!"
     * echo Locale::translate('unknown.key');            // Output: "unknown.key"
     */
    public static function translate(string $key, ?array $params = null): string
    {
        self::init();
        // include
        if (!isset($GLOBALS['DICTIONARY'])) {
            $path = self::$locale->path . '/' . self::$locale->lang . '.php';
            $GLOBALS['DICTIONARY'] = file_exists($path) ? include $path : [];
        }
        // return
        $value = Tool::arrayNestedValue($GLOBALS['DICTIONARY'], explode('.', $key));

        if (is_string($value) && !empty($value)) {
            return empty($params) ? $value : sprintf($value, ...$params);
        } else {
            return $key;
        }
    }
}
