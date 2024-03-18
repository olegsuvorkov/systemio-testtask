<?php

namespace App\Tests\Service\ExchangeRate;

use App\Service\ExchangeRate\Client\ClientInterface;
use App\Service\ExchangeRate\ExchangeRateService;
use App\Service\ExchangeRate\ExchangeRateServiceInterface;
use App\Service\ExchangeRate\Store\StoreInterface;
use App\Tests\Service\ExchangeRate\Store\DatabaseTestCaseTrait;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[UsesClass(ExchangeRateService::class)]
#[TestDox('')]
class ExchangeRateServiceTest extends KernelTestCase
{
    use DatabaseTestCaseTrait;

    private StoreInterface $store;

    private ClientInterface & MockObject $client;

    private ExchangeRateServiceInterface $exchangeRateService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->client = $this->createMock(ClientInterface::class);
        self::getContainer()->set(ClientInterface::class, $this->client);
        $this->store = self::getContainer()->get(StoreInterface::class);
        $this->store->clear();
        $this->exchangeRateService = self::getContainer()->get(ExchangeRateServiceInterface::class);
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    #[Test]
    public function getRateFull(): void
    {
        $expectedRates = ['USD' => 1.09, 'RUB' => 100.00];
        $date = new DateTimeImmutable();
        $this->client
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['USD', 'RUB'], $this->equalToWithDelta($date, 1))
            ->willReturn($expectedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB']);
        self::assertEqualsWithDelta($expectedRates, $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function getRateSingle(): void
    {
        $existRates = ['USD' => 1.09, 'RUB' => 100.00];
        $date = new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
        $this->store->save('EUR', $date, $existRates);
        $unresolvedRates = ['GBP' => 12.03];
        $this->client
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['GBP'], $this->equalToWithDelta($date, 1))
            ->willReturn($unresolvedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP']);
        self::assertEqualsWithDelta([...$existRates, ...$unresolvedRates], $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function getRateByDateFull(): void
    {
        $expectedRates = ['USD' => 1.091, 'RUB' => 100.01];
        $date = new DateTimeImmutable('2000-12-12');
        $this->client
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['USD', 'RUB'], $this->equalToWithDelta($date, 1))
            ->willReturn($expectedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB'], $date);
        self::assertEqualsWithDelta($expectedRates, $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function getRateByDateSingle(): void
    {
        $date = new DateTimeImmutable('2000-12-12');
        $existRates = ['USD' => 1.091, 'RUB' => 100.01];
        $this->store->save('EUR', $date, $existRates);
        $expectedRates = ['GBP' => 12.032];
        $this->client
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['GBP'], $this->equalToWithDelta($date, 1))
            ->willReturn($expectedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP'], $date);
        self::assertEqualsWithDelta([...$existRates, ...$expectedRates], $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }
}
