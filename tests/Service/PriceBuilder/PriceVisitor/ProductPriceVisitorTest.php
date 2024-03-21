<?php

namespace App\Tests\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponFixed;
use App\Entity\CouponPercent;
use App\Entity\Product;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceVisitor\DiscountFixedPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use App\Service\PriceBuilder\PriceVisitor\ProductPriceVisitor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class ProductPriceVisitorTest extends AbstractPriceVisitorTestCase
{
    private ExchangeRateServiceInterface & MockObject $exchangeRateService;


    protected function setUp(): void
    {
        self::bootKernel();
        $this->exchangeRateService = self::createMock(ExchangeRateServiceInterface::class);
        self::getContainer()->set(ExchangeRateServiceInterface::class, $this->exchangeRateService);
        $this->priceVisitor = self::getContainer()->get(ProductPriceVisitor::class);
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

        $product = new Product();
        $product->price = 100.0;
        $product->currency = 'RUB';
        $price = $this->callCalculatePrice(100.0, 'USD', $product);
        self::assertEqualsWithDelta(101.0, $price->getPrice(), 0.0001);
    }

    #[Test]
    public function calculatePriceExchangeRateFailure()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('convert')
            ->with('RUB', 'USD', 100.0)
            ->willThrowException(new UnexpectedExchangeRateException([], ['USD']));

        $product = new Product();
        $product->price = 100.0;
        $product->currency = 'RUB';
        self::expectException(PriceBuilderException::class);
        $this->callCalculatePrice(100.0, 'USD', $product);
    }

    #[Test]
    public function calculatePriceFailure()
    {
        $coupon = (object) [];
        self::expectException(PriceBuilderException::class);
        $this->callCalculatePrice(100.0, 'USD', $coupon);
    }
}
