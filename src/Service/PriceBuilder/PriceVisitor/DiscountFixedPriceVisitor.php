<?php

namespace App\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponFixed;
use App\Service\ExchangeRate\Exception\ExchangeRateException;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('discount_fixed')]
readonly class DiscountFixedPriceVisitor implements PriceVisitorInterface
{
    public function __construct(
        private ExchangeRateServiceInterface $service,
    )
    {
    }

    public function getPriority(): int
    {
        return 200;
    }

    public function calculatePrice(PriceBuilderInterface $priceBuilder, mixed $data): void
    {
        if (!$data instanceof CouponFixed) {
            throw new PriceBuilderException();
        }
        try {
            $discount = $this->service->convert($data->currency, $priceBuilder->getCurrency(), $data->price);
        } catch (ExchangeRateException $e) {
            throw new PriceBuilderException(previous: $e);
        }
        $priceBuilder->setPrice($priceBuilder->getPrice() - $discount);
    }
}
