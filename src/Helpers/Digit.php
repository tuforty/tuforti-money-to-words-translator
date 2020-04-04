<?php

namespace Tuforti\MoneyToWords\Helpers;

class Digit
{
    /**
     * Determines if a given numeric value is a decimal.
     *
     * @param String|Numeric $value Value to check if decimal
     * 
     * @return boolean
     */
    static function isDecimal($value)
    {
        return fmod($value, 1) !== 0.0;
    }
}
