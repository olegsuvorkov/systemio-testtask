<?php

namespace App\Tests\Service\PriceBuilder\PriceVisitor;

use App\Entity\Product;
use App\Service\PriceBuilder\PriceBuilderFactoryInterface;
use App\Service\PriceBuilder\PriceInterface;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractPriceVisitorTestCase extends KernelTestCase
{
    protected PriceBuilderFactoryInterface $priceBuilderFactory;

    protected PriceVisitorInterface $priceVisitor;

    protected function setUp(): void
    {
        $this->priceBuilderFactory = self::getContainer()->get(PriceBuilderFactoryInterface::class);
    }

    protected function callCalculatePrice(float $price, string $currency, mixed $data): PriceInterface
    {
        $price = $this->createPrice($price, $currency);
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $this->priceVisitor->calculatePrice($priceBuilder, $data);
        return $price;
    }

    protected function createPrice(float $price, string $currency): PriceInterface
    {
        return new class($price, $currency) implements PriceInterface
        {

            public function __construct(
                private float $price,
                private readonly string $currency,
            )
            {
            }

            public function getCurrency(): string
            {
                return $this->currency;
            }

            public function getPrice(): float
            {
                return $this->price;
            }

            public function setPrice(float $price): void
            {
                $this->price = $price;
            }
        };
    }
}
