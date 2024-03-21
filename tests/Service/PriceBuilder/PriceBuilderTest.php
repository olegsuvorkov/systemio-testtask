<?php

namespace App\Tests\Service\PriceBuilder;

use App\Entity\CouponPercent;
use App\Entity\Product;
use App\Service\PriceBuilder\CouponProviderInterface;
use App\Service\PriceBuilder\PriceBuilderFactoryInterface;
use App\Service\PriceBuilder\PriceBuilderInterface;
use App\Service\PriceBuilder\PriceInterface;
use App\Service\PriceBuilder\PriceVisitor\DiscountFixedPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\DiscountPercentPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use App\Service\PriceBuilder\PriceVisitor\ProductPriceVisitor;
use App\Service\PriceBuilder\PriceVisitor\TaxNumberPriceVisitor;
use App\Service\PriceBuilder\ProductProviderInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PriceBuilderTest extends KernelTestCase
{
    #[Test]
    public function buildPrice(): void
    {
        $container = self::getContainer();
        $result = [];
        $callback = function (PriceBuilderInterface $priceBuilder, mixed $data) use (&$result) {
            $result[] = $data;
        };
        $container->set(DiscountPercentPriceVisitor::class, $this->createPriceVisitor($callback, 200));
        $container->set(DiscountFixedPriceVisitor::class, $this->createPriceVisitor($callback, 300, self::never()));
        $container->set(ProductPriceVisitor::class, $this->createPriceVisitor($callback, 1000));
        $container->set(TaxNumberPriceVisitor::class, $this->createPriceVisitor($callback, 100));
        $product1 = new Product();
        $product2 = new Product();
        $productProvider = self::createMock(ProductProviderInterface::class);
        $productProvider->expects(self::exactly(2))
            ->method('getProduct')
            ->willReturn($product1, $product2);
        $container->set(ProductProviderInterface::class, $productProvider);

        $coupon = new CouponPercent('abc');
        $couponProvider = self::createMock(CouponProviderInterface::class);
        $couponProvider->expects(self::once())
            ->method('getCoupon')
            ->willReturn($coupon);
        $container->set(CouponProviderInterface::class, $couponProvider);

        /** @var PriceBuilderFactoryInterface $priceBuilderFactory */
        $priceBuilderFactory = self::getContainer()->get(PriceBuilderFactoryInterface::class);
        $price = self::createMock(PriceInterface::class);
        $priceBuilder = $priceBuilderFactory->createPriceBuilder($price);
        $priceBuilder->addCouponCode('abc');
        $priceBuilder->addProduct(4);
        $priceBuilder->setTaxNumber('FR1234');
        $priceBuilder->addProduct(5);
        $priceBuilder->buildPrice();
        self::assertEquals([
            $product1,
            $product2,
            $coupon,
            'FR1234'
        ], $result);
    }

    private function createPriceVisitor(callable $callback, int $priority, ?InvocationOrder $order = null): PriceVisitorInterface
    {
        $priceVisitor = self::createMock(PriceVisitorInterface::class);
        $priceVisitor
            ->expects($order ?? self::any())
            ->method('calculatePrice')
            ->willReturnCallback($callback);
        $priceVisitor
            ->expects(self::any())
            ->method('getPriority')
            ->willReturn($priority);
        return $priceVisitor;
    }
}
