<?php

namespace App\Tests\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponFixed;
use App\Entity\CouponPercent;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderFactoryInterface;
use App\Service\PriceBuilder\PriceVisitor\DiscountFixedPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\DiscountPercentPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class DiscountPercentPriceVisitorTest extends AbstractPriceVisitorTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        $this->priceVisitor = self::getContainer()->get(DiscountPercentPriceVisitor::class);
        parent::setUp();
    }

    #[Test]
    public function calculatePriceSuccess()
    {
        $price = $this->createPrice(200.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $coupon = new CouponPercent('abc123');
        $coupon->percent = 10.0;
        $this->priceVisitor->calculatePrice($priceBuilder, $coupon);
        self::assertEqualsWithDelta(180.0, $price->getPrice(), 0.0001);
    }

    #[Test]
    public function calculatePriceFailure()
    {
        $price = $this->createPrice(100.0, 'USD');
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $coupon = new CouponFixed('abc123');
        self::expectException(PriceBuilderException::class);
        $this->priceVisitor->calculatePrice($priceBuilder, $coupon);
    }
}
