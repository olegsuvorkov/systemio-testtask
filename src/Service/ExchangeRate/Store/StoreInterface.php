<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Store;

use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use DateTimeInterface;

interface StoreInterface
{
    /**
     * @param string $from
     * @param array $to
     * @param DateTimeInterface $date
     * @return array
     * @throws UnexpectedExchangeRateException
     */
    public function load(string $from, array $to, DateTimeInterface $date): array;

    /**
     * @param string $from
     * @param DateTimeInterface $date
     * @param array $rates
     * @return void
     */
    public function save(string $from, DateTimeInterface $date, array $rates): void;

    /**
     * @return void
     */
    public function clear(): void;
}
