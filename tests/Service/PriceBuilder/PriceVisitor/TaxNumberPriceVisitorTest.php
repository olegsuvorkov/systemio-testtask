<?php

namespace App\Tests\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponFixed;
use App\Entity\CouponPercent;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceVisitor\DiscountFixedPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use App\Service\PriceBuilder\PriceVisitor\TaxNumberPriceVisitor;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxRule\TaxRuleInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class TaxNumberPriceVisitorTest extends AbstractPriceVisitorTestCase
{
    private TaxRuleInterface & MockObject $taxRule;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->taxRule = self::createMock(TaxRuleInterface::class);
        self::getContainer()->set(TaxRuleInterface::class, $this->taxRule);
        $this->priceVisitor = self::getContainer()->get(TaxNumberPriceVisitor::class);
        parent::setUp();
    }

    #[Test]
    public function calculatePriceSuccess()
    {
        $this->taxRule
            ->expects(self::once())
            ->method('calculatePrice')
            ->with('FRAZ1234', 100.0)
            ->willReturn(119.0);
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $this->priceVisitor->calculatePrice($priceBuilder, 'FRAZ1234');
        self::assertEqualsWithDelta(119.0, $price->getPrice(), 0.0001);
    }

    #[Test]
    public function calculatePriceFailure()
    {
        $this->taxRule
            ->expects(self::once())
            ->method('calculatePrice')
            ->with('FR1234', 100.0)
            ->willThrowException(new UnknownTaxFormatException());
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        self::expectException(PriceBuilderException::class);
        $this->priceVisitor->calculatePrice($priceBuilder, 'FR1234');
    }
}
