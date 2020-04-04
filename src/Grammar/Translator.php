<?php

namespace Tuforti\MoneyToWords\Grammar;

use Closure;
use Exception;
use Tuforti\MoneyToWords\Contracts\Cache;
use Google\Cloud\Translate\V2\TranslateClient;
use Tuforti\MoneyToWords\Languages as Language;

class Translator
{
    /**
     * Google translator.
     * 
     * @var Stichoza\GoogleTranslate\GoogleTranslate
     */
    protected $translator;

    /**
     * The language of the money value (numeral) inserted.
     * 
     * @var string
     */
    protected $languageTo;

    /**
     * Translation cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Create a new translation client.
     *
     * @param string $googlAuthKey Authentication key for google translate
     * @param Language $languageTo Destination Language to use for translation
     */
    function __construct(string $googleAuthKey, $cache, String $languageTo)
    {
        $this->cache = $cache;
        $this->languageTo = $languageTo;
        $this->translator = new TranslateClient(['key' => $googleAuthKey]);
    }

    /**
     * Set the language of translation.
     * 
     * @param Language $languageTo Language to translate into
     * 
     * @return void
     */
    public function setLanguage(Language $languageTo)
    {
        $this->$languageTo = $languageTo;
    }

    /**
     * Get the language used for translation.
     *
     * @return Tuforti\MoneyToWords\Languages
     */
    public function getDestinationLanguage()
    {
        return $this->languageTo;
    }

    /**
     * Get all the content in the cache.
     *
     * @return array
     */
    public function getCacheContent()
    {
        return $this->cache::getAll();
    }

    /**
     * Translates the money input into previously configured language.
     * 
     * @param String $text Text to translate
     * 
     * @return text
     * @throws Exception
     */
    public function translate(String $text, string $key = null, $source = Language::ENGLISH)
    {
        return $this->getOrTranslate(
            $text,
            $this->languageTo,
            function ($text, $language) use ($source) {
                $payload = ['target' => $language, 'source' => $source];
                $translation = $this->translator->translate($text, $payload);
                return $translation['text'];
            },
            $key
        );
    }

    /**
     * Translates a given text into the language specified.
     * 
     * NOTE: Don't delete this function, as it is passed as a callback,
     * which is the reason why it's still marked as unused by the text
     * editor.
     * 
     * @param String $text   Text to translate
     * @param String $language Language to convert into
     * 
     * @return void
     */
    private function translateTo($moneyInNumeric, $language)
    {
        $tempLanguageTo = $this->languageTo;
        try {
            $this->languageTo = $language;
            return $this->translate($moneyInNumeric, null, null);
        } finally {
            $this->languageTo = $tempLanguageTo;
        }
    }

    /**
     * Get translation from cache or translate and store in cache.
     * 
     * If a cache key is provided, the key is used instead of the text.
     *
     * @param string|int $text
     * @param string $language
     * @param Closure $translate
     * @param string $cacheKey
     * @return string|null
     */
    public function getOrTranslate(
        string $text,
        string $language,
        Closure $translate,
        string $cacheKey = null
    ) {
        $key = $cacheKey ?? $text;
        $cached = $this->cache::get($key, $language);
        if ($cached) return $cached;

        $translation = $translate($text, $language);
        if ($translation) {
            $this->cache::set($key, $language, $translation);
            return $translation;
        }
    }

    /**
     * Translates the money value to english.
     * 
     * @param String $string Text to translate
     * 
     * @return String Translated text in english
     */
    public function toEnglish($string)
    {
        return $this->getOrTranslate(
            $string,
            Language::ENGLISH,
            $this->translateTo
        );
    }

    /**
     * Translates the money value to arabic.
     * 
     * @param String $string Text to translate
     * 
     * @return String Translated text in greek
     */
    public function toArabic($string)
    {
        return $this->getOrTranslate(
            $string,
            Language::ARABIC,
            $this->translateTo
        );
    }
}
