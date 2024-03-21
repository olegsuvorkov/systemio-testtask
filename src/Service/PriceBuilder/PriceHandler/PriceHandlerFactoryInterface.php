<?php

namespace App\Service\PriceBuilder\PriceHandler;

interface PriceHandlerFactoryInterface
{
    public function createPriceHandler(string $name, mixed $data): PriceHandler;
}