<?php declare(strict_types=1);

namespace App\DTO;

use App\Service\PriceBuilder\PriceInterface;

class Price implements PriceInterface
{
    private float $price;

    public function __construct(
        private readonly string $currency,
    )
    {
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}