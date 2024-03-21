<?php

namespace App\Tests\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponFixed;
use App\Entity\CouponPercent;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceVisitor\DiscountFixedPriceVisitor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class DiscountFixedPriceVisitorTest extends AbstractPriceVisitorTestCase
{
    private ExchangeRateServiceInterface & MockObject $exchangeRateService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->exchangeRateService = self::createMock(ExchangeRateServiceInterface::class);
        self::getContainer()->set(ExchangeRateServiceInterface::class, $this->exchangeRateService);
        $this->priceVisitor = self::getContainer()->get(DiscountFixedPriceVisitor::class);
        parent::setUp();
    }

    #[Test]
    public function calculatePriceSuccess()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('convert')
            ->with('RUB', 'USD', 100.0)
            ->willReturn(1.0);
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $coupon = new CouponFixed('abc123');
        $coupon->price = 100.0;
        $coupon->currency = 'RUB';
        $this->priceVisitor->calculatePrice($priceBuilder, $coupon);
        self::assertEqualsWithDelta(99.0, $price->getPrice(), 0.0001);
    }

    #[Test]
    public function calculatePriceExchangeRateFailure()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('convert')
            ->with('RUB', 'USD', 100.0)
            ->willThrowException(new UnexpectedExchangeRateException([], ['USD']));
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $coupon = new CouponFixed('abc123');
        $coupon->price = 100.0;
        $coupon->currency = 'RUB';
        self::expectException(PriceBuilderException::class);
        $this->priceVisitor->calculatePrice($priceBuilder, $coupon);
    }

    #[Test]
    public function calculatePriceFailure()
    {
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $coupon = new CouponPercent('abc123');
        self::expectException(PriceBuilderException::class);
        $this->priceVisitor->calculatePrice($priceBuilder, $coupon);
    }
}
