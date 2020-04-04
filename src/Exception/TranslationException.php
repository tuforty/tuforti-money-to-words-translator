<?php

namespace Tuforti\MoneyToWords\Exception;

use Throwable;
use Exception;

class TranslationException extends Exception
{
    protected $metadata;

    function __construct($message, $code = 0, $metadata = [], Throwable $previous = null)
    {
        parent::__construct($message, $code = $code, $previous);
        $this->metadata = $metadata;
    }

    /**
     * Get exception metadata.
     */
    public function getMetaData()
    {
        return $this->metadata;
    }
}
