<?php

namespace App\Service\Tax\TaxProvider;

use App\Service\Tax\Exception\UnknownTaxFormatException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.tax.tax_provider')]
interface TaxProviderInterface
{
    /**
     * @param string $countryCode
     * @param string $format
     * @return float
     *
     * @throws UnknownTaxFormatException
     */
    public function getTaxPercentByCountryCodeAndFormat(string $countryCode, string $format): float;
}