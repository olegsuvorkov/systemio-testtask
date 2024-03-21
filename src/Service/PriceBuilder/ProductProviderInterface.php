<?php declare(strict_types=1);

namespace App\Service\PriceBuilder;

use App\Entity\Product;

interface ProductProviderInterface
{
    /**
     * @param int $id
     * @return Product
     */
    public function getProduct(int $id): Product;
}