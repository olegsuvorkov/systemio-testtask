<?php declare(strict_types=1);

namespace App\Service\ExchangeRate;

use App\Service\ExchangeRate\Client\ClientInterface;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use App\Service\ExchangeRate\Store\StoreInterface;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Throwable;

/**
 * Фасад для получения доступа к курсам обмена валют
 *
 * Результаты полученные из базы данных, обращаемся к клиенту толь при их отсутствии
 * и сохраняем что бы не обращаться повторно
 */
#[AsAlias]
readonly class ExchangeRateService implements ExchangeRateServiceInterface
{
    public function __construct(
        private ClientInterface $client,
        private StoreInterface  $store,
        private LockFactory     $lockFactory,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getRate(string $from, array $to, DateTimeInterface $date = new DateTimeImmutable()): array
    {
        $timezone = new DateTimeZone('+00:00');
        $date = DateTimeImmutable::createFromInterface($date)->setTimezone($timezone);
        try {
            $rates = $this->store->load($from, $to, $date);
        } catch (UnexpectedExchangeRateException $previous) {
            $rates = array_fill_keys($to, null);
            $rates = [...$rates, ...$previous->rates];
            [$locked, $expected] = $this->createLocks($from, $previous->unexpectedCurrencies, $date);
            if ($expected) {
                try {
                    $newRates = $this->client->getByDate($from, array_keys($expected), $date);
                    $this->store->save($from, $date, $newRates);
                    $rates = [...$rates, ...$newRates];
                } catch (UnexpectedExchangeRateException $e) {
                    $this->store->save($from, $date, $e->rates);
                    throw new UnexpectedExchangeRateException([...$rates, ...$e->rates], $e->unexpectedCurrencies, $e);
                } finally {
                    foreach ($expected as $lock) {
                        $lock->release();
                    }
                }
            }
            $this->acquireUnlock($locked);
            $newRates = $this->store->load($from, array_keys($locked), $date);
            $rates = [...$rates, ...$newRates];
            UnexpectedExchangeRateException::throwIfExistUnexpected($rates);
        }
        return $rates;
    }

    /**
     * @param string $from
     * @param string[] $to
     * @param DateTimeInterface $date
     * @return array{array<string, LockInterface>, array<string, LockInterface>}
     */
    private function createLocks(string $from, array $to, DateTimeInterface $date): array
    {
        $suffix = $date->format('Y-m-d');
        $locks = [];
        foreach ($to as $currency) {
            $locks[$currency] = $this->lockFactory->createLock($from.'_'.$currency.'_'.$suffix, autoRelease: false);
        }
        /** @var array{array<string, LockInterface>, array<string, LockInterface>} $result */
        $result = [[], []];
        foreach ($locks as $currency => $lock) {
            $result[(int) $lock->acquire()][$currency] = $lock;
        }
        return $result;
    }

    /**
     * Дожидаемся разблокировки результатов
     *
     * @param LockInterface[] $list
     * @return void
     */
    private function acquireUnlock(array $list): void
    {
        $callback = static function (LockInterface $lock) {
            return !$lock->acquire();
        };
        while ($list = array_filter($list, $callback)) {
            try {
                $rand = random_int(-10, 10);
            } catch (Throwable) {
                $rand = rand(-10, 10);
            }
            usleep((100 + $rand) * 1000);
        }
    }
}