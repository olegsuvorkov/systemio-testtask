<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Продукт
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public string $name = '';

    #[ORM\Column(columnDefinition: 'FLOAT NOT NULL CHECK(price > 0.0)')]
    public float $price = 0.0;

    #[ORM\Column(length: 3, options: [
        'fixed' => true,
    ])]
    public string $currency = '';
}
