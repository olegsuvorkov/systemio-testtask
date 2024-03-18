<?php declare(strict_types=1);

namespace App\Service\ExchangeRate;

use App\Service\ExchangeRate\Exception\ExchangeRateException;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Доступ к курсам обмена валют
 */
#[Autoconfigure(public: true)]
interface ExchangeRateServiceInterface
{
    /**
     * Получить курсы обмена валют за указанную дату
     *
     * @param string $from
     * @param string[] $to
     * @param DateTimeInterface $date
     * @return array<string, float>
     * @throws ExchangeRateException
     */
    public function getRate(string $from, array $to, DateTimeInterface $date = new DateTimeImmutable()): array;
}
