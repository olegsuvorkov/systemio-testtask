<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaxRepository::class)]
#[ORM\UniqueConstraint(fields: ['countryCode', 'format'])]
class Tax
{
    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 3, options: [
        'fixed' => true,
    ])]
    public string $countryCode = '';

    #[ORM\Column(length: 64)]
    public string $format = '';

    #[ORM\Column(columnDefinition: "FLOAT CHECK(percent > 0.0)")]
    public float $percent = 0.0;
}
