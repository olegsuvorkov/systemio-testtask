<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Client;

use App\Service\ExchangeRate\Exception\ExchangeRateException;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Клиент для работы с сервисами курсов валют
 */
#[AutoconfigureTag('service.exchange_rate.client')]
interface ClientInterface
{
    /**
     * Получить курс обмена валют за конкретную дату
     *
     * @param string $from
     * @param string[] $to
     * @param DateTimeInterface $date
     * @return array<string, float>
     * @throws ExchangeRateException
     **/
    public function getByDate(string $from, array $to, DateTimeInterface $date): array;
}
