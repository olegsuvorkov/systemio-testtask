<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->name = 'Iphone';
        $product->price = 100.0;
        $product->currency = 'EUR';
        $manager->persist($product);

        $product = new Product();
        $product->name = 'Наушники';
        $product->price = 20.0;
        $product->currency = 'EUR';
        $manager->persist($product);

        $product = new Product();
        $product->name = 'Чехол';
        $product->price = 10.0;
        $product->currency = 'EUR';
        $manager->persist($product);

        $manager->flush();
    }
}
