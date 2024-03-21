<?php declare(strict_types=1);

namespace App\Service\PriceBuilder;

interface PriceBuilderFactoryInterface
{
    public function createPriceBuilder(PriceInterface $price): PriceBuilderInterface;
}