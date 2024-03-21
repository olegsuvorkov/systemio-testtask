<?php

namespace App\Service\PriceBuilder;

use App\Entity\Coupon;

interface CouponProviderInterface
{
    public function getCoupon(string $code): Coupon;
}