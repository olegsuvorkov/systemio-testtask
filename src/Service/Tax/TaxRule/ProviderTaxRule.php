<?php

namespace App\Service\Tax\TaxRule;

use App\Service\Tax\Exception\InvalidTaxNumberException;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxProvider\TaxProviderInterface;

readonly class ProviderTaxRule implements TaxRuleInterface
{
    public const string EXPR_NUMBER = '~^([a-zA-Z]{2})([a-zA-Z0-9]+)$~';

    public function __construct(
        private TaxProviderInterface $taxProvider,
    )
    {
    }

    public function checkNumber(string $number): bool
    {
        try {
            $this->getTaxPercent($number);
            return true;
        } catch (InvalidTaxNumberException) {
            return false;
        }
    }

    /**
     * @param string $number
     * @param float $price
     * @return float
     * @throws InvalidTaxNumberException
     */
    public function calculatePrice(string $number, float $price): float
    {
        $percent = $this->getTaxPercent($number);
        return $price * (1 + $percent / 100);
    }

    /**
     * @throws InvalidTaxNumberException
     */
    private function getTaxPercent(string $number): float
    {
        if (preg_match(self::EXPR_NUMBER, $number, $matches)) {
            $countryCode = strtoupper($matches[1]);
            $format = $matches[2];
            for ($i = strlen($format) - 1; $i >= 0; $i--) {
                $format[$i] = ctype_digit($format[$i]) ? 'X' : 'Y';
            }
            try {
                return $this->taxProvider->getTaxPercentByCountryCodeAndFormat($countryCode, $format);
            } catch (UnknownTaxFormatException $e) {
                throw new InvalidTaxNumberException(previous: $e);
            }
        }
        throw new InvalidTaxNumberException();
    }
}
