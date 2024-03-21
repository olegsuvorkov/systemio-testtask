<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CouponFixed extends Coupon
{
    #[ORM\Column(columnDefinition: "FLOAT CHECK(price > 0.0 OR type <> 'fixed')")]
    public float $price = 0.0;

    #[ORM\Column(length: 3, options: [
        'fixed' => true,
    ])]
    public string $currency = '';

    public function getType(): string
    {
        return 'discount_fixed';
    }
}