<?php

namespace App\Service\PriceBuilder;

interface PriceInterface
{
    public function getCurrency(): string;

    public function getPrice(): float;

    public function setPrice(float $price): void;
}
