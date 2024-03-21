<?php

namespace App\Service\PriceBuilder\PriceVisitor;

use App\Entity\Product;
use App\Service\ExchangeRate\Exception\ExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('product')]
readonly class ProductPriceVisitor implements PriceVisitorInterface
{
    public function __construct(
        private ExchangeRateServiceInterface $exchangeRateService,
    )
    {
    }

    public function getPriority(): int
    {
        return 1000;
    }

    public function calculatePrice(PriceBuilderInterface $priceBuilder, mixed $data): void
    {
        if (!$data instanceof Product) {
            throw new PriceBuilderException();
        }
        try {
            $price = $this->exchangeRateService->convert($data->currency, $priceBuilder->getCurrency(), $data->price);
        } catch (ExchangeRateException $e) {
            throw new PriceBuilderException(previous: $e);
        }
        $priceBuilder->setPrice($priceBuilder->getPrice() + $price);
    }
}