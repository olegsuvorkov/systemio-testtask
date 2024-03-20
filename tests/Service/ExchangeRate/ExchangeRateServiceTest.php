<?php

namespace App\Tests\Service\ExchangeRate;

use App\Service\ExchangeRate\Client\ClientCollection;
use App\Service\ExchangeRate\Client\ClientInterface;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
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

    private ClientCollection $collection;

    private ClientInterface & MockObject $clientFirst;

    private ClientInterface & MockObject $clientSecond;

    private ExchangeRateServiceInterface $exchangeRateService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->clientFirst = $this->createMock(ClientInterface::class);
        $this->clientSecond = $this->createMock(ClientInterface::class);
        $this->collection = new ClientCollection([$this->clientFirst]);
        self::getContainer()->set(ClientCollection::class, $this->collection);
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
        $this->clientFirst
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
        $this->clientFirst
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['GBP'], $this->equalToWithDelta($date, 1))
            ->willReturn($unresolvedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP']);
        self::assertEqualsWithDelta([...$existRates, ...$unresolvedRates], $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function getRateMultipleFull(): void
    {
        $this->collection->add($this->clientSecond);
        $expectedRates = ['USD' => 1.09, 'RUB' => 100.00];
        $date = new DateTimeImmutable();

        $this->clientFirst
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['USD', 'RUB'], $this->equalToWithDelta($date, 1))
            ->willThrowException(new UnexpectedExchangeRateException(['USD' => 1.09], ['RUB']));

        $this->clientSecond
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['RUB'], $this->equalToWithDelta($date, 1))
            ->willReturn(['RUB' => 100.00]);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB']);
        self::assertEqualsWithDelta($expectedRates, $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function getRateMultiplePartFailure(): void
    {
        $this->collection->add($this->clientSecond);
        $date = new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
        $this->clientFirst
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['USD', 'RUB', 'GBP'], $this->equalToWithDelta($date, 1))
            ->willThrowException(new UnexpectedExchangeRateException(['USD' => 1.09], ['RUB', 'GBP']));

        $this->clientSecond
            ->expects(self::once())
            ->method('getByDate')

            ->with('EUR', ['RUB', 'GBP'], $this->equalToWithDelta($date, 1))
            ->willThrowException(new UnexpectedExchangeRateException(['RUB' => 100.00], ['GBP']));

        try {
            $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP']);
            self::fail();
        } catch (UnexpectedExchangeRateException $e) {
            self::assertEquals(['GBP'], $e->unexpectedCurrencies);
            self::assertEqualsWithDelta(['USD' => 1.09, 'RUB' => 100.00], $e->rates, 0.0001);
        }
    }

    #[Test]
    public function getRateMultiplePart(): void
    {
        $this->collection->add($this->clientSecond);
        $existRates = ['GBP' => 10.09];
        $date = new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
        $this->store->save('EUR', $date, $existRates);
        $this->clientFirst
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['USD', 'RUB'], $this->equalToWithDelta($date, 1))
            ->willThrowException(new UnexpectedExchangeRateException(['USD' => 1.09], ['RUB']));

        $this->clientSecond
            ->expects(self::once())
            ->method('getByDate')

            ->with('EUR', ['RUB'], $this->equalToWithDelta($date, 1))
            ->willReturn(['RUB' => 100.00]);

        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP']);
        self::assertEqualsWithDelta(['USD' => 1.09, 'RUB' => 100.00, 'GBP' => 10.09], $rates, 0.0001);
    }

    #[Test]
    public function getRateByDateFull(): void
    {
        $expectedRates = ['USD' => 1.091, 'RUB' => 100.01];
        $date = new DateTimeImmutable('2000-12-12');
        $this->clientFirst
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
        $this->clientFirst
            ->expects(self::once())
            ->method('getByDate')
            ->with('EUR', ['GBP'], $this->equalToWithDelta($date, 1))
            ->willReturn($expectedRates);
        $rates = $this->exchangeRateService->getRate('EUR', ['USD', 'RUB', 'GBP'], $date);
        self::assertEqualsWithDelta([...$existRates, ...$expectedRates], $rates, 0.0001);
        self::assertEqualsExchangeRateByDate($rates);
    }

    #[Test]
    public function convert(): void
    {
        $date = new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
        $existRates = ['USD' => 1.09];
        $this->store->save('EUR', $date, $existRates);
        $price = $this->exchangeRateService->convert('EUR', 'USD', 100.0, $date);
        self::assertEqualsWithDelta(109.0, $price, 0.0001);
    }
}
