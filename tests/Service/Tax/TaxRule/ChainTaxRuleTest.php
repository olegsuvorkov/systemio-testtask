<?php

namespace App\Tests\Service\Tax\TaxRule;

use App\Service\Tax\Exception\InvalidTaxNumberException;
use App\Service\Tax\TaxRule\ChainTaxRule;
use App\Service\Tax\TaxRule\TaxRuleInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChainTaxRuleTest extends KernelTestCase
{
    /**
     * @var TaxRuleInterface[]
     */
    private static array $stubRules = [];

    public static function setUpBeforeClass(): void
    {
        $stubRuleFirst = self::createStub(TaxRuleInterface::class);
        $stubRuleFirst
            ->method('checkNumber')
            ->willReturnMap([
                ['RU1234', true],
                ['FRAB43', false],
                ['DE4321', false],
                ['IT23AZ', false],
            ]);
        $stubRuleFirst
            ->method('calculatePrice')
            ->willReturnMap([
                ['RU1234', 10.0, 11.0],
            ]);
        self::$stubRules[] = $stubRuleFirst;

        $stubRuleSecond = self::createStub(TaxRuleInterface::class);
        $stubRuleSecond
            ->method('checkNumber')
            ->willReturnMap([
                ['RU1234', false],
                ['FRAB43', true],
                ['DE4321', false],
                ['IT23AZ', false],
            ]);
        $stubRuleSecond
            ->method('calculatePrice')
            ->willReturnMap([
                ['FRAB43', 12.0, 13.0],
            ]);
        self::$stubRules[] = $stubRuleSecond;

        $stubRuleThird = self::createStub(TaxRuleInterface::class);
        $stubRuleThird
            ->method('checkNumber')
            ->willReturnMap([
                ['RU1234', false],
                ['FRAB43', false],
                ['DE4321', true],
                ['IT23AZ', false],
            ]);
        $stubRuleThird
            ->method('calculatePrice')
            ->willThrowException(new InvalidTaxNumberException());
        self::$stubRules[] = $stubRuleThird;
    }

    #[Test]
    public function checkNumber(): void
    {
        $taxRule = new ChainTaxRule(self::$stubRules);
        self::assertTrue($taxRule->checkNumber('RU1234'));
        self::assertTrue($taxRule->checkNumber('FRAB43'));
        self::assertTrue($taxRule->checkNumber('DE4321'));
        self::assertFalse($taxRule->checkNumber('IT23AZ'));
    }

    #[Test]
    public function calculatePriceSuccess(): void
    {
        $taxRule = new ChainTaxRule(self::$stubRules);
        self::assertEqualsWithDelta(11.0, $taxRule->calculatePrice('RU1234', 10.0), 0.001);
        self::assertEqualsWithDelta(13.0, $taxRule->calculatePrice('FRAB43', 12.0), 0.001);
    }

    #[Test]
    public function calculatePriceUndefinedFormatFailure(): void
    {
        $taxRule = new ChainTaxRule(self::$stubRules);
        self::expectException(InvalidTaxNumberException::class);
        $taxRule->calculatePrice('DE4321', 12.0);
    }

    #[Test]
    public function calculatePriceFailureInvalidNumberFailure(): void
    {
        $taxRule = new ChainTaxRule(self::$stubRules);
        self::expectException(InvalidTaxNumberException::class);
        $taxRule->calculatePrice('IT23AZ', 13.0);
    }
}
