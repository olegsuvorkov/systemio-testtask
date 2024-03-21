<?php

namespace App\Service\PriceBuilder;

interface PriceBuilderFactoryInterface
{
    public function createPriceBuilder(PriceInterface $price): PriceBuilderInterface;
}