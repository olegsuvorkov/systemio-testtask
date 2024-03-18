<?php

namespace App\Tests\Service\ExchangeRate\Store;

use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\Store\CacheStore;
use App\Service\ExchangeRate\Store\PgSqlDatabaseStore;
use App\Service\ExchangeRate\Store\StoreInterface;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CacheStoreTest extends KernelTestCase
{
    private StoreInterface $originalStore;

    private CacheStore $store;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->originalStore = self::createMock(StoreInterface::class);
        self::getContainer()->set(PgSqlDatabaseStore::class, $this->originalStore);
        $this->store = self::getContainer()->get(CacheStore::class);
    }

    #[Test]
    public function load()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 2.0002];
        $this->originalStore
            ->expects(self::once())
            ->method('load')
            ->with('USD', ['EUR', 'RUB'], self::equalToWithDelta(self::now(), 1))
            ->willReturn($expected);
        $actual = $this->store->load('USD', ['EUR', 'RUB'], self::now());
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
        $actual = $this->store->load('USD', ['EUR', 'RUB'], self::now());
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
    }

    #[Test]
    public function save()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 2.0002];
        $this->store->save('USD', self::now(), $expected);
        $actual = $this->store->load('USD', ['EUR', 'RUB'], self::now());
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
    }

    #[Test]
    public function loadWithException()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 2.0002];
        $this->store->save('USD', self::now(), $expected);
        $unexpectedException = new UnexpectedExchangeRateException(['GBP' => 3.0003], ['AED']);
        $this->originalStore
            ->expects(self::once())
            ->method('load')
            ->with('USD', ['GBP', 'AED'], self::equalToWithDelta(self::now(), 1))
            ->willThrowException($unexpectedException);
        try {
            $this->store->load('USD', ['EUR', 'RUB', 'GBP', 'AED'], self::now());
            self::fail();
        } catch (UnexpectedExchangeRateException $e) {
            self::assertEqualsWithDelta([...$expected, ...$unexpectedException->rates], $e->rates, 0.0001);
            self::assertEquals($unexpectedException->unexpectedCurrencies, $e->unexpectedCurrencies, 0.0001);
        }
    }

    private static function now(): DateTimeInterface
    {
        return new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
    }
}
