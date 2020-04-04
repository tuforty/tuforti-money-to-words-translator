<?php

namespace Tuforti\MoneyToWords\Contracts;

use Tuforti\MoneyToWords\Languages;

abstract class Cache
{
    static $cache = [];

    public static function getAll()
    {
        return static::$cache;
    }

    /**
     * Get the caching key for a translation
     *
     * @param int $moneyValue
     * @param Languages $languageTo
     * @return string
     */
    private static function key($moneyValue, $languageTo)
    {
        return "{$languageTo}-{$moneyValue}";
    }

    /**
     * Get translation from cache.
     *
     * @param int moneyInGreekNumeric
     * @param Languages $languageTo
     * @return string
     */
    public static function get($moneyValue, $languageTo)
    {
        $key  = static::key($moneyValue, $languageTo);
        return static::$cache[$key] ?? null;
    }

    /**
     * Set tranlation
     *
     * @param int $moneyValue
     * @param Languages $languageTo
     * @param string $translation
     * @return boolean $translation
     */
    public static function set($moneyValue, $languageTo, $translation)
    {
        $key  = static::key($moneyValue, $languageTo);
        static::$cache[$key] = $translation;
        return $key;
    }
}
