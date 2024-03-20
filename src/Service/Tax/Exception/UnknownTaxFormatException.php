<?php

namespace App\Service\Tax\Exception;

use Throwable;

class UnknownTaxFormatException extends \Exception
{
    public static function create(string $countryCode, string $format, ?Throwable $previous = null): self
    {
        return new static(sprintf("Unknown tax number format `%s` for country `%s`", $format, $countryCode), previous: $previous);
    }
}
