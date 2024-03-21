<?php declare(strict_types=1);

namespace App\Service\PriceBuilder\PriceHandler;

interface PriceHandlerFactoryInterface
{
    public function createPriceHandler(string $name, mixed $data): PriceHandler;
}