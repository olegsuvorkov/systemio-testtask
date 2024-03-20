<?php

namespace App\Tests\Service\Tax\TaxProvider;

use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxProvider\CacheTaxProvider;
use App\Service\Tax\TaxProvider\TaxProviderInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheTaxProviderTest extends KernelTestCase
{
    private static ArrayAdapter $cache;

    private static TaxProviderInterface $taxProviderSuccess;

    private static TaxProviderInterface $taxProviderFailure;

    public static function setUpBeforeClass(): void
    {
        self::$cache = new ArrayAdapter();

        self::$taxProviderSuccess = self::createStub(TaxProviderInterface::class);
        self::$taxProviderSuccess
            ->method('getTaxPercentByCountryCodeAndFormat')
            ->willReturn(10.0);

        self::$taxProviderFailure = self::createStub(TaxProviderInterface::class);
        self::$taxProviderFailure
            ->method('getTaxPercentByCountryCodeAndFormat')
            ->willThrowException(UnknownTaxFormatException::create('RU', '1234'));
    }

    #[Test]
    public function failureGetTaxPercentByCountryCodeAndFormat(): void
    {
        $provider = new CacheTaxProvider(self::$taxProviderFailure, self::$cache);
        self::expectException(UnknownTaxFormatException::class);
        $provider->getTaxPercentByCountryCodeAndFormat('RU', '1234');
    }

    #[Test]
    public function successGetTaxPercentByCountryCodeAndFormat(): void
    {
        $provider = new CacheTaxProvider(self::$taxProviderSuccess, self::$cache);
        $value = $provider->getTaxPercentByCountryCodeAndFormat('RU', '1234');
        self::assertEqualsWithDelta(10.0, $value, 0.001);

        $provider = new CacheTaxProvider(self::$taxProviderFailure, self::$cache);
        $value = $provider->getTaxPercentByCountryCodeAndFormat('RU', '1234');
        self::assertEqualsWithDelta(10.0, $value, 0.001);
    }
}
