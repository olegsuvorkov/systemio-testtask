<?php

namespace App\Tests\Service\ExchangeRate\Store;

trait DatabaseTestCaseTrait
{
    private static function assertEqualsExchangeRateByDate(array $expected): void
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        $actual = $connection->fetchAllKeyValue('SELECT "to", "rate" FROM "exchange_rate"');
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
    }
}
