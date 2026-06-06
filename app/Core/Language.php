<?php

namespace App\Core;

class Language
{
    private static $translations = [];
    private static $currentLang = 'en';

    public static function load()
    {
        self::$currentLang = $_SESSION['lang'] ?? 'en';

        $filePath = __DIR__ . "/../Lang/" . self::$currentLang . ".php";
        if (file_exists($filePath)) {
            self::$translations = require $filePath;
        } else {
            self::$translations = require __DIR__ . "/../Lang/en.php";
        }
    }

    public static function set($lang)
    {
        if (in_array($lang, ['en', 'fr'])) {
            $_SESSION['lang'] = $lang;
            self::$currentLang = $lang;
            self::load();
        }
    }

    public static function get($key)
    {
        if (empty(self::$translations)) {
            self::load();
        }
        return self::$translations[$key] ?? $key;
    }

    public static function getCurrent()
    {
        return self::$currentLang;
    }
}
