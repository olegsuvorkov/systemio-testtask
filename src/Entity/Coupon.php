<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'percent' => CouponPercent::class,
    'fixed'   => CouponFixed::class,
])]
#[ORM\UniqueConstraint(fields: ['code'])]
abstract class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column]
    public ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 32)]
        public string $code,
    )
    {
    }

    public abstract function getType(): string;
}
