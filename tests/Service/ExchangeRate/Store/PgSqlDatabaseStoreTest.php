<?php

namespace App\Tests\Service\ExchangeRate\Store;

use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\Store\PgSqlDatabaseStore;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[UsesClass(PgSqlDatabaseStore::class)]
class PgSqlDatabaseStoreTest extends KernelTestCase
{
    use DatabaseTestCaseTrait;

    private PgSqlDatabaseStore $store;

    protected function setUp(): void
    {
        /** @var ContainerInterface $container */
        $container = self::getContainer();
        $this->store = $container->get(PgSqlDatabaseStore::class);
        $this->store->clear();
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    #[Test]
    public function saveWithoutDuplicate()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 20.0002, 'GBP' => 10.0003];
        $this->store->save('USD', self::now(), $expected);
        self::assertEqualsExchangeRateByDate($expected);
    }

    #[Test]
    public function saveWithDuplicate()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 20.0002, 'GBP' => 10.0003];
        $this->store->save('USD', self::now(), $expected);
        $expected = [...$expected, 'RUB' => 21.0001];
        $this->store->save('USD', self::now(), $expected);
        self::assertEqualsExchangeRateByDate($expected);
    }

    #[Test]
    public function loadNotExists()
    {
        $this->store->save('USD', self::now(), ['GBP' => 1.0001]);
        try {
            $this->store->load('USD', ['EUR', 'RUB'], self::now());
            self::fail();
        } catch (UnexpectedExchangeRateException $e) {
            self::assertEmpty($e->rates);
            self::assertEquals(['EUR', 'RUB',], $e->unexpectedCurrencies, 0.0001);
        }
    }

    #[Test]
    public function loadExists()
    {
        $expected = ['EUR' => 1.0001, 'RUB' => 20.0002];
        $this->store->save('USD', self::now(), [...$expected, 'GBP' => 10.0003]);
        $actual = $this->store->load('USD', ['EUR', 'RUB'], self::now());
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
    }

    private static function now(): DateTimeInterface
    {
        return new DateTimeImmutable(timezone: new DateTimeZone('+00:00'));
    }
}
