<?php

namespace App\Service\PriceBuilder;

use App\Service\PriceBuilder\Exception\PriceBuilderException;

interface PriceBuilderInterface
{
    /**
     * @param int $productId
     * @return self
     * @throws PriceBuilderException
     */
    public function addProduct(int $productId): self;

    /**
     * @param string $couponCode
     * @return self
     * @throws PriceBuilderException
     */
    public function addCouponCode(string $couponCode): self;

    /**
     * @param string $taxNumber
     * @return self
     * @throws PriceBuilderException
     */
    public function setTaxNumber(string $taxNumber): self;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @param float $price
     * @return self
     */
    public function setPrice(float $price): self;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return self
     * @throws PriceBuilderException
     */
    public function buildPrice(): self;
}
