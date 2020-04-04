<?php

/**
 * Autoload files using Composer autoload
 */

use PHPUnit\Framework\TestCase;
use Tuforti\MoneyToWords\Converter;
use Tuforti\MoneyToWords\Languages as Language;

class EnglishConversionTest extends TestCase
{
    protected $converter;

    protected function setUp(): void
    {
        $this->converter = new Converter("", "naira", "kobo", Language::ENGLISH);
    }

    public function wholeNumberDataProvider()
    {
        return [
            ["345", [
                'whole' => "three hundred and forty-five",
                'decimal' => null,
                'full' => "three hundred and forty-five naira only",
            ]],
            ["34", [
                'whole' => "thirty-four",
                'decimal' => null,
                'full' => "thirty-four naira only",
            ]],
            ["23455", [
                'whole' => "twenty-three thousand, four hundred and fifty-five",
                'decimal' => null,
                'full' => "twenty-three thousand, four hundred and fifty-five naira only",
            ]],
            ["345003", [
                'whole' => "three hundred and forty-five thousand, three",
                'decimal' => null,
                'full' => "three hundred and forty-five thousand, three naira only",
            ]],
            ["475923455", [
                'whole' => "four hundred and seventy-five million, nine hundred and twenty-three thousand, four hundred and fifty-five",
                'decimal' => null,
                'full' => "four hundred and seventy-five million, nine hundred and twenty-three thousand, four hundred and fifty-five naira only",
            ]],
        ];
    }

    /**
     * @dataProvider wholeNumberDataProvider
     */
    public function testWholeNumber($wholeNumber, $expectedMessage)
    {
        $result = $this->converter->convert($wholeNumber);
        $this->assertEquals($expectedMessage['whole'], $result->whole);
        $this->assertEquals($expectedMessage['decimal'], $result->decimal);
        $this->assertEquals($expectedMessage['full'], $result->full);
    }

    public function largeNumbersDataProvider()
    {
        return [
            ["50000000", [
                'whole' => "fifty million",
                'decimal' => null,
                'full' => "fifty million naira only",
            ]],
            ["900000000000", [
                'whole' => "nine hundred billion",
                'decimal' => null,
                'full' => "nine hundred billion naira only",
            ]],
            ["900070000000", [
                'whole' => "nine hundred billion, seventy million",
                'decimal' => null,
                'full' => "nine hundred billion, seventy million naira only",
            ]],
        ];
    }

    /**
     * @dataProvider largeNumbersDataProvider
     */
    public function testLargeNumbers($largeNumber, $expectedMessage)
    {
        $result = $this->converter->convert($largeNumber);
        $this->assertEquals($expectedMessage['whole'], $result->whole);
        $this->assertEquals($expectedMessage['decimal'], $result->decimal);
        $this->assertEquals($expectedMessage['full'], $result->full);
    }

    public function numberWithZeroPrefixDataProvider()
    {
        return [
            ["0000000", [
                'whole' => "",
                'decimal' => "",
                'full' => "",
            ]],
            ["050003", [
                'whole' => "fifty thousand, three",
                'decimal' => null,
                'full' => "fifty thousand, three naira only",
            ]],
            ["050303", [
                'whole' => "fifty thousand, three hundred and three",
                'decimal' => null,
                'full' => "fifty thousand, three hundred and three naira only",
            ]],
            ["005475923455", [
                'whole' => "five billion, four hundred and seventy-five million, nine hundred and twenty-three thousand, four hundred and fifty-five",
                'decimal' => null,
                'full' => "five billion, four hundred and seventy-five million, nine hundred and twenty-three thousand, four hundred and fifty-five naira only",
            ]],
        ];
    }

    /**
     * @dataProvider numberWithZeroPrefixDataProvider
     */
    public function testNumberWithZeroPrefix($numberWithZeroPrefix, $expectedMessage)
    {
        $result = $this->converter->convert($numberWithZeroPrefix);
        $this->assertEquals($expectedMessage['whole'], $result->whole);
        $this->assertEquals($expectedMessage['decimal'], $result->decimal);
        $this->assertEquals($expectedMessage['full'], $result->full);
    }

    public function decimalNumberDataProvider()
    {
        return [
            ["23.0", [
                'whole' => "twenty-three",
                'decimal' => null,
                'full' => "twenty-three naira only",
            ]],
            ["345003.09", [
                'whole' => "three hundred and forty-five thousand, three",
                'decimal' => "nine",
                'full' => "three hundred and forty-five thousand, three naira, nine kobo only",
            ]],
            ["233464773.457", [
                'whole' => "two hundred and thirty-three million, four hundred and sixty-four thousand, seven hundred and seventy-three",
                'decimal' => "forty-six",
                'full' => "two hundred and thirty-three million, four hundred and sixty-four thousand, seven hundred and seventy-three naira, forty-six kobo only",
            ]],
        ];
    }

    /**
     * @dataProvider decimalNumberDataProvider
     */
    public function testDecimalNumber($decimalNumber, $expectedMessage)
    {
        $result = $this->converter->convert($decimalNumber);
        $this->assertEquals($expectedMessage['whole'], $result->whole);
        $this->assertEquals($expectedMessage['decimal'], $result->decimal);
        $this->assertEquals($expectedMessage['full'], $result->full);
    }
}
