<?php

namespace App\Service\PriceBuilder;

interface PriceItemInterface
{
    public function getPrice(): float;

    public function getCurrency(): string;
}