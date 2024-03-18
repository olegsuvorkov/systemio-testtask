<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Exception;

use Throwable;

class UnexpectedExchangeRateException extends ExchangeRateException
{
    public function __construct(
        public readonly array $rates,
        public readonly array $unexpectedCurrencies,
        ?Throwable $previous = null
    ) {
        parent::__construct(previous: $previous);
    }

    /**
     * @param array<string, float|null> $rates
     * @return void
     * @throws self
     */
    public static function throwIfExistUnexpected(array $rates, ?Throwable $previous = null): void
    {
        if ($unexpectedCurrencies = array_keys($rates, null, true)) {
            $rates = array_filter($rates, is_float(...));
            throw new self($rates, $unexpectedCurrencies, $previous);
        }
    }
}
