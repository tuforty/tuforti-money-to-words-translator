<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tuforti\MoneyToWords\Converter;
use Tuforti\MoneyToWords\Contracts\Cache;

$converter = new Converter(
    "some random secret key",
    "naira",
    "kobo",
    $languageTo = "en",
    $cache = Cache::class
);

var_dump($converter->convert("八百七十二万七千八百二十四"));
var_dump($converter->convert(23.45));
var_dump($converter->convert(748247284782));
var_dump($converter->convert(748247284782.34));
var_dump($converter->convert('34'));
var_dump($converter->convert('2345.34'));
var_dump($converter->convert('3453345'));
