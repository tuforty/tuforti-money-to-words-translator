<?php

/**
 * Coverts a money value to senetence
 * 
 * @category Money
 * @author   Tochukwu Nkemdilim <nkemdilimtochukwu@gmail.com>
 * 
 * Date: 11:22am June-17-2017
 * ALL THE GLORY BE TO CHRIST JESUS.
 */

namespace Tuforti\MoneyToWords;

use Exception;
use Tuforti\MoneyToWords\Helpers\Digit;
use Tuforti\MoneyToWords\Contracts\Cache;
use Tuforti\MoneyToWords\Grammar\Translator;
use Tuforti\MoneyToWords\Languages as Language;
use Tuforti\MoneyToWords\Helpers\NumericSystem;
use Google\Cloud\Core\Exception\ServiceException;
use Tuforti\MoneyToWords\Grammar\SentenceGenerator;
use Tuforti\MoneyToWords\Contracts\TranslationLexemes;
use Tuforti\MoneyToWords\Exception\TranslationException;

/**
 * USAGE:
 * $converter = new Converter("naira",  "kobo");
 * echo ($converter->convert(345));
 *
 */
class Converter
{
    /**
     * Whole number section of the monetary value to convert.
     * 
     * A list of numeric systems can be found at: 
     * https://en.wikipedia.org/wiki/List_of_numeral_systems
     * 
     * @var Numeric
     */
    protected $moneyWholePart;

    /**
     * Decimal number section of the monetary value to convert.
     * 
     * @var Numeric
     */
    protected $moneyDecimalPart;

    /**
     * Language translator.
     * 
     * @var Tuforti\MoneyToWords\Grammar\Translator
     */
    protected $translator;


    /**
     * Is the given monetary value a decimal?
     *
     * @var boolean
     */
    protected $isDecimal = false;

    /**
     * Currency to use for whole number part of the given monetary value.
     * 
     * @var String
     */
    protected $currencyForWhole;

    /**
     * Currency to use for decimal part of the given monetary value.
     *
     * @var String
     */
    protected $currencyForDecimal;

    /**
     * Original money value provided by the user.
     *
     * @var Numeric
     */
    protected $money;

    /**
     * Create a new money to word converter.
     * 
     * @param String $googleAuthKey      Google translate authentication key
     * @param String $currencyForWhole   Currency for whole number part of money
     * @param String $languageTo         Language to convert money in words to
     * @param String $currencyForDecimal Currency to use for decimal part of the given monetary value.
     * @param Cache $cache               Cache implementation for translation.
     */
    function __construct(
        string $googleAuthKey,
        $currencyForWhole,
        $currencyForDecimal,
        $languageTo = Language::ENGLISH,
        $cache = Cache::class
    ) {
        $this->setCurrency(trim($currencyForWhole), trim($currencyForDecimal));
        $this->translator = new Translator($googleAuthKey, $cache, trim($languageTo));
    }

    /**
     * Get the language which money values are translated into.
     * 
     * @return Language Translation language
     */
    public function getTransalationLanguage()
    {
        return $this->translator->getDestinationLanguage();
    }

    /**
     * Set a new currency for money value.
     * 
     * @param String $currencyForWhole   Currency in word fo whole money part e.g. naira, dollar, pounds, yens etc.
     * @param String $currencyForDecimal Currency in word e.g. cent (assuming dollar is passed as `$currencyForWhole`)
     * 
     * @return void
     */
    public function setCurrency($currencyForWhole, $currencyForDecimal = '')
    {
        $this->currencyForWhole = trim($currencyForWhole);
        $this->currencyForDecimal = trim($currencyForDecimal);
    }

    /**
     * Set the language of translation.
     * 
     * @param String $languageTo Language to translate into
     * 
     * @return void
     */
    public function setLanguage(Language $languageTo)
    {
        $this->translator->setLanguage(trim($languageTo));
    }

    /**
     * Get the translator object.
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Set a new money value for convertion.
     * 
     * @param String $moneyValue Money value to convert
     * 
     * @return void
     */
    private function _setMoney(String $moneyValue)
    {
        $this->money = $moneyValue = trim($moneyValue);
        // Translate into greek numeric system of 0 - 9.
        if (!NumericSystem::isGreek($moneyValue)) {
            $moneyValue = $this->translator->toArabic($moneyValue);
        }

        $moneyValue = number_format($moneyValue, 2, '.', '');

        $isDecimal = Digit::isDecimal($moneyValue);

        if ($isDecimal) {
            $values = explode('.', $moneyValue);

            $this->moneyDecimalPart = intval($values[1]);
            $this->moneyWholePart = intval($values[0]);
        } else {
            $this->moneyWholePart = $moneyValue;
        }

        $this->isDecimal = $isDecimal;
    }

    /**
     * Performs the conversion of the given movey value from digit to words.
     * 
     * @param String $moneyValue Money value of any language, in which should be converted to words
     * 
     * @return TranslationLexemes LexeConverted sentence
     * @throws ServiceException|Exception
     */
    public function convert($moneyValue)
    {
        try {
            $this->_setMoney($moneyValue);
            if ($this->isDecimal) {
                return $this->_convertWholeAndDecimalPart();
            }

            return $this->_convertWholePart();
        } catch (ServiceException $ex) {
            $error = json_decode($ex->getMessage())->error;
            throw new TranslationException($error->message, $ex->getCode(), $ex->getPrevious());
        } catch (Exception $ex) {
            throw new TranslationException($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
    }

    /**
     * Converts the money value specified into sentence, given that the money 
     * value is a whole number and not a decimal.
     *
     * @return TranslationLexemes|null
     */
    private function _convertWholePart()
    {
        $whole = SentenceGenerator::generateSentence($this->moneyWholePart);
        if (trim($whole) == '') return TranslationLexemes::zero();

        $full = "{$whole} {$this->currencyForWhole} only";
        if ($this->_translationIsEnglish()) {
            return new TranslationLexemes($whole, null, $full);
        }

        return new TranslationLexemes(
            $this->translator->translate($whole, $this->moneyWholePart),
            null,
            $this->translator->translate($full, $this->money)
        );
    }

    /**
     * Converts the money value specified into sentence, given that the money 
     * value is a decimal.
     *
     * @return TranslationLexemes|null
     */
    private function _convertWholeAndDecimalPart()
    {
        $whole = trim(SentenceGenerator::generateSentence($this->moneyWholePart));
        $decimal = trim(SentenceGenerator::generateSentence($this->moneyDecimalPart));
        if ($whole == '' && $decimal == '') return TranslationLexemes::zero();

        $full = "";
        if ($whole != "") $full = "{$whole} {$this->currencyForWhole}";
        if ($whole != "" && $decimal != "") $full .= ', ';
        if ($decimal != "") $full .= "{$decimal} {$this->currencyForDecimal}";
        $full .= " only";

        if ($this->_translationIsEnglish()) {
            return new TranslationLexemes($whole, $decimal, $full);
        }

        return new TranslationLexemes(
            $this->translator->translate($whole, $this->moneyWholePart),
            $this->translator->translate($decimal, $this->moneyDecimalPart),
            $this->translator->translate($full, $this->money)
        );
    }

    /**
     * Is english the current language for translation.
     *
     * @return boolean
     */
    private function _translationIsEnglish(): bool
    {
        return $this->getTransalationLanguage() == Language::ENGLISH;
    }
}
