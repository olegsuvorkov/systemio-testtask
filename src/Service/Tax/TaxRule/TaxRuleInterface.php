<?php

namespace App\Service\Tax\TaxRule;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.tax.tax_rule')]
interface TaxRuleInterface
{
    public function checkNumber(string $number): bool;

    public function calculatePrice(string $number, float $price): float;
}