<?php

namespace Tuforti\MoneyToWords\Grammar;

class SentenceLexer
{
    /**
     * Text to be analyzed.
     *
     * @var string
     */
    protected $text;

    /**
     * Current cursor position during lexer analysis.
     *
     * @var integer
     */
    protected $position = 0;

    /**
     * Initialize the lexer object.
     *
     * @param string $text
     */
    function __construct(string $text = "")
    {
        $this->text = $text;
    }

    /**
     * Lex the text into the equivalent string value.
     */
    function lex(): string
    {
        $value = "";

        while ($this->_canLookAhead()) {
            if ($this->_isWhitespace()) {
                $this->_skipWhitespaces();
            } else {
                $value .= $this->_extractNumber();
            }

            $this->position++;
        }

        return $value;
    }

    /**
     * Skip all subsequent whitespaces.
     */
    function _skipWhitespaces()
    {
        while ($this->_canLookAhead() && $this->_isWhitespace()) {
            $this->position++;
        }
    }

    /**
     * Handle a number from a sequence of characters from the 
     * current lexer cusrsor.
     */
    function _extractNumber(): string
    {
        $fragment = "";

        while ($this->_canLookAhead() && !$this->_isWhitespace()) {
            $fragment .= strtolower($this->text[$this->position]);
            $this->position++;
        }


        $result = $this->_identifyNumber($fragment);
        return  $result == false ? "" :  $result;
    }

    /**
     * Identify the integer value that is equal to the fragment specified.
     *
     * @param string $fragment
     * @return boolean|int
     */
    function _identifyNumber(string $fragment)
    {
        if ($this->_isNoisy($fragment)) return false;

        if ($tens = $this->_isTens($fragment)) {
            $this->_skipWhitespaces();
            $unit = intval($this->_extractNumber());
            return  $tens + $unit;
        }

        return $this->_isUnit($fragment);
    }

    /**
     * Check if the fragment text is an unwated text.
     *
     * @param string $fragment
     * @return boolean
     */
    function _isNoisy(string $fragment): bool
    {
        return $this->_isSeperator($fragment) ||
            $this->_isStandardUnit($fragment);
    }

    /**
     * Check if the current fragment is a text
     *
     * @param string $fragment
     * @return boolean
     */
    function _isSeperator(string $fragment): bool
    {
        switch ($fragment) {
            case 'and':
                return true;

            default:
                return false;
        }
    }

    /**
     * Check if the current cursor character is a whitespace.
     *
     * @return boolean
     */
    function _isWhitespace(): bool
    {
        return $this->text[$this->position] == " ";
    }

    /**
     * Check if we can look ahead/progress our cursoor.
     *
     * @param integer $step
     * @return boolean
     */
    function _canLookAhead($step = 0): bool
    {
        return $this->position + $step < strlen($this->text);
    }

    /**
     * Check if a fragment is a currency unit e.g. million, thousand etc.
     *
     * @param string $fragment
     * @return boolean
     */
    function _isStandardUnit(string $fragment): bool
    {
        switch ($fragment) {
            case "trillion":
            case "billion":
            case "million":
            case "thousand":
            case "hundred":
            case "million":
            case "milliard":
            case "billion":
            case "billiard":
            case "trillion":
            case "quadrillion":
            case "quintillion":
            case "sextillion":
            case "septillion":
            case "octillion":
            case "nonillion":
            case "decillion":
            case "undecillion":
            case "duodecillion":
            case "tredecillion":
            case "quattuordecillion":
            case "quindecillion":
            case "sexdecillion":
            case "septendecillion":
            case "octodecillion":
            case "novemdecillion":
            case "vigintillion":
            case "centillio":
                return true;

            default:
                return false;
        }
    }

    /**
     * Check if a fragment is a tens digit e.g. seventy, eighty etc.
     *
     * @param string $fragment
     * @return integer
     */
    function _isTens(string $fragment): int
    {
        switch ($fragment) {
            case 'ten':
                return 10;
            case "twenty":
                return 20;
            case "thirty":
                return 30;
            case "forty":
                return 40;
            case "fifty":
                return 50;
            case "sixty":
                return 60;
            case "seventy":
                return 70;
            case "eighty":
                return 80;
            case "ninety":
                return 90;
            default:
                return false;
        }
    }

    /**
     * Check if a fragment is a units digit e.g. one, two etc.
     *
     * @param string $fragment
     * @return integer
     */
    function _isUnit($fragment)
    {
        switch ($fragment) {
            case 'zero':
                return 0;
            case "one":
                return 1;
            case "two":
                return 2;
            case "three":
                return 3;
            case "four":
                return 4;
            case "five":
                return 5;
            case "six":
                return 6;
            case "seven":
                return 7;
            case "eight":
                return 8;
            case "nine":
                return 9;
            default:
                return false;
        }
    }
}
