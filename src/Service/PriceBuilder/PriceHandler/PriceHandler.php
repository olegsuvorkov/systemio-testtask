<?php

namespace App\Service\PriceBuilder\PriceHandler;

use App\Service\PriceBuilder\PriceBuilderInterface;
use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class PriceHandler
{
    public function __construct(
        private PriceVisitorInterface $priceVisitor,
        private mixed                 $data,
    )
    {
    }

    public function handle(PriceBuilderInterface $price): void
    {
        $this->priceVisitor->calculatePrice($price, $this->data);
    }

    public function getPriority(): int
    {
        return $this->priceVisitor->getPriority();
    }

    public function getPriceVisitor(): PriceVisitorInterface
    {
        return $this->priceVisitor;
    }
}