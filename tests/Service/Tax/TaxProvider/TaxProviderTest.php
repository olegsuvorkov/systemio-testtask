<?php

namespace App\Tests\Service\Tax\TaxProvider;

use App\Entity\Tax;
use App\Repository\TaxRepository;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxProvider\TaxProvider;
use App\Service\Tax\TaxProvider\TaxProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaxProviderTest extends KernelTestCase
{
    private const string COUNTRY_CODE = 'RU';
    private const string FORMAT = 'XXXX';

    private TaxRepository $taxRepository;

    private TaxProviderInterface $taxProvider;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->em = $managerRegistry->getManagerForClass(Tax::class);
        $this->taxRepository = $this->em->getRepository(Tax::class);
        $this->taxProvider = new TaxProvider($this->taxRepository);
    }

    #[Test]
    public function failureGetTaxPercentByCountryCodeAndFormat(): void
    {
        $tax = $this->taxRepository->findByCountryCodeAndFormat(self::COUNTRY_CODE, self::FORMAT);
        if ($tax !== null) {
            $this->em->remove($tax);
        }
        self::expectException(UnknownTaxFormatException::class);
        $this->taxProvider->getTaxPercentByCountryCodeAndFormat(self::COUNTRY_CODE, self::FORMAT);
    }

    #[Test]
    public function successGetTaxPercentByCountryCodeAndFormat(): void
    {
        $tax = new Tax();
        $tax->countryCode = 'RU';
        $tax->format = 'XXXX';
        $tax->percent = 10.0;
        $this->em->persist($tax);
        $this->em->flush();
        $percent = $this->taxProvider->getTaxPercentByCountryCodeAndFormat(self::COUNTRY_CODE, self::FORMAT);
        self::assertEqualsWithDelta(10.0, $percent, 0.001);
        $this->em->remove($tax);
        $this->em->flush();
    }
}
