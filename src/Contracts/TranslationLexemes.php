<?php

namespace Tuforti\MoneyToWords\Contracts;


class TranslationLexemes
{
    public $whole;

    public $decimal;

    public $full;

    function __construct($whole = null, $decimal = null, $full = null)
    {
        $this->whole = $whole;
        $this->decimal = $decimal;
        $this->full = $full;
    }

    public static function zero()
    {
        return new self('', '', '');
    }
}
