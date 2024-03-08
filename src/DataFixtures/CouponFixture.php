<?php

namespace App\DataFixtures;

use App\Entity\CouponFixed;
use App\Entity\CouponPercent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $coupon1 = new CouponFixed('5V26kJtK');
        $coupon1->price = 10.0;
        $coupon1->currency = 'USD';
        $manager->persist($coupon1);

        $coupon2 = new CouponFixed('47Pdsyze');
        $coupon2->price = 5.0;
        $coupon2->currency = 'EUR';
        $manager->persist($coupon2);

        $coupon3 = new CouponPercent('Spcd95Vz');
        $coupon3->percent = 7.5;
        $manager->persist($coupon3);

        $coupon4 = new CouponPercent('kzYiyAXw');
        $coupon4->percent = 15;
        $manager->persist($coupon4);

        $manager->flush();
    }
}
