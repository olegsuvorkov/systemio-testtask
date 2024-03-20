<?php

namespace App\Service\Tax\TaxProvider;

use App\Repository\TaxRepository;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use Throwable;

readonly class TaxProvider implements TaxProviderInterface
{
    public function __construct(
        private TaxRepository $taxRepository,
    )
    {
    }

    /**
     * @param string $countryCode
     * @param string $format
     * @return float
     *
     * @throws UnknownTaxFormatException
     */
    public function getTaxPercentByCountryCodeAndFormat(string $countryCode, string $format): float
    {
        try {
            $tax = $this->taxRepository->findByCountryCodeAndFormat($countryCode, $format);
            if ($tax !== null) {
                return $tax->percent;
            }
        } catch (Throwable $exception) {
            throw UnknownTaxFormatException::create($countryCode, $format, $exception);
        }
        throw UnknownTaxFormatException::create($countryCode, $format);
    }
}
