<?php declare(strict_types=1);

namespace App\Service\Tax\TaxProvider;

use App\Service\Tax\Exception\UnknownTaxFormatException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

#[AsAlias(id: TaxProviderInterface::class)]
readonly class CacheTaxProvider implements TaxProviderInterface
{
    public function __construct(
        #[Autowire(service: TaxProvider::class)]
        private TaxProviderInterface $taxProvider,
        #[Target('tax.cache')]
        private CacheInterface $cache,
    )
    {
    }

    /**
     * @param string $countryCode
     * @param string $format
     * @return float
     * @throws InvalidArgumentException
     * @throws UnknownTaxFormatException
     */
    public function getTaxPercentByCountryCodeAndFormat(string $countryCode, string $format): float
    {
        return $this->cache->get($countryCode.$format, function () use ($countryCode, $format) {
            return $this->taxProvider->getTaxPercentByCountryCodeAndFormat($countryCode, $format);
        });
    }
}