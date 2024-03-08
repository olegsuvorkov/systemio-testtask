<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Tax;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaxFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tax1 = new Tax();
        $tax1->countryCode = 'DE';
        $tax1->format = 'XXXXXXXXX';
        $tax1->percent = 19.0;
        $manager->persist($tax1);

        $tax2 = new Tax();
        $tax2->countryCode = 'IT';
        $tax2->format = 'XXXXXXXXXXX';
        $tax2->percent = 22.0;
        $manager->persist($tax2);

        $tax3 = new Tax();
        $tax3->countryCode = 'GR';
        $tax3->format = 'XXXXXXXXX';
        $tax3->percent = 24.0;
        $manager->persist($tax3);

        $tax4 = new Tax();
        $tax4->countryCode = 'FR';
        $tax4->format = 'YYXXXXXXXXX';
        $tax4->percent = 20.0;
        $manager->persist($tax4);

        $manager->flush();
    }
}
