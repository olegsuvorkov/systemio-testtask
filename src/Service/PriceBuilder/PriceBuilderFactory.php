<?php

namespace App\Service\PriceBuilder;

use App\Repository\CouponRepository;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\PriceHandler\PriceHandlerFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias]
readonly class PriceBuilderFactory implements PriceBuilderFactoryInterface
{
    public function __construct(
        private PriceHandlerFactoryInterface $priceHandlerFactory,
        private CouponProviderInterface      $couponProvider,
        private ProductProviderInterface     $productProvider,
    )
    {
    }

    public function createPriceBuilder(PriceInterface $price): PriceBuilderInterface
    {
        return new PriceBuilder(
            $this->priceHandlerFactory,
            $this->couponProvider,
            $this->productProvider,
            $price
        );
    }
}