<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CouponPercent extends Coupon
{
    #[ORM\Column(columnDefinition: "FLOAT CHECK((percent > 0.0 AND type = 'percent') OR (percent IS NULL AND type <> 'percent'))")]
    public float $percent = 0.0;
}