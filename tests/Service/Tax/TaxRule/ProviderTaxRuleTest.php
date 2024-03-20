<?php

namespace App\Tests\Service\Tax\TaxRule;

use App\Service\Tax\Exception\InvalidTaxNumberException;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxProvider\TaxProviderInterface;
use App\Service\Tax\TaxRule\ProviderTaxRule;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProviderTaxRuleTest extends KernelTestCase
{
    private static TaxProviderInterface $stubProviderSuccess;

    private static TaxProviderInterface $stubProviderFailure;

    public static function setUpBeforeClass(): void
    {
        self::$stubProviderSuccess = self::createStub(TaxProviderInterface::class);
        self::$stubProviderSuccess
            ->method('getTaxPercentByCountryCodeAndFormat')
            ->willReturnMap([
                ['RU', 'XXXX', 10.0],
                ['FR', 'XXYY', 12.0],
            ]);

        self::$stubProviderFailure = self::createStub(TaxProviderInterface::class);
        self::$stubProviderFailure
            ->method('getTaxPercentByCountryCodeAndFormat')
            ->willThrowException(UnknownTaxFormatException::create('RU', '1234'));
    }

    #[Test]
    public function checkNumberSuccess(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderSuccess);
        self::assertTrue($taxRule->checkNumber('RU1234'));
        self::assertTrue($taxRule->checkNumber('FR12AB'));
    }

    #[Test]
    public function checkNumberUndefinedFormatFailure(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderFailure);

        self::assertFalse($taxRule->checkNumber('RU1234'));
    }

    #[Test]
    public function checkNumberInvalidNumberFailure(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderFailure);
        self::assertFalse($taxRule->checkNumber('4321'));
    }

    #[Test]
    public function calculatePriceSuccess(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderSuccess);
        $actual = $taxRule->calculatePrice('RU1234', 10.0);
        self::assertEqualsWithDelta(11.0, $actual, 0.001);
        $actual = $taxRule->calculatePrice('FR12AB', 50.0);
        self::assertEqualsWithDelta(56.0, $actual, 0.001);
    }

    #[Test]
    public function calculatePriceUndefinedFormatFailure(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderFailure);
        self::expectException(InvalidTaxNumberException::class);
        $taxRule->calculatePrice('RU1234', 10.0);
    }

    #[Test]
    public function calculatePriceInvalidNumberFailure(): void
    {
        $taxRule = new ProviderTaxRule(self::$stubProviderFailure);
        self::expectException(InvalidTaxNumberException::class);
        $taxRule->calculatePrice('4321', 10.0);
    }
}
