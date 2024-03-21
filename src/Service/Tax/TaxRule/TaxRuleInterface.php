<?php

namespace App\Service\Tax\TaxRule;

use App\Service\Tax\Exception\InvalidTaxNumberException;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.tax.tax_rule')]
interface TaxRuleInterface
{
    public function checkNumber(string $number): bool;

    /**
     * @param string $number
     * @param float $price
     * @return float
     * @throws InvalidTaxNumberException
     * @throws UnknownTaxFormatException
     */
    public function calculatePrice(string $number, float $price): float;
}