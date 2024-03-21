<?php declare(strict_types=1);

namespace App\Service\PriceBuilder;

use App\Entity\Coupon;

interface CouponProviderInterface
{
    public function getCoupon(string $code): Coupon;
}