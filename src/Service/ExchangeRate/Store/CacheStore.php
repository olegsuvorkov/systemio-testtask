<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Store;

use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsDecorator(decorates: StoreInterface::class)]
readonly class CacheStore implements StoreInterface
{
    public function __construct(
        #[AutowireDecorated]
        private StoreInterface         $original,
        #[Target('exchange_rate.cache')]
        private CacheItemPoolInterface $cache,
    )
    {
    }

    public function load(string $from, array $to, DateTimeInterface $date): array
    {
        $result = array_fill_keys($to, null);
        /** @var CacheItemInterface[] $cacheItems */
        $cacheItems = [];
        foreach ($to as $currency) {
            $item = $this->cache->getItem($this->getKey($from, $currency, $date));
            if ($item->isHit()) {
                $result[$currency] = $item->get();
            } else {
                $cacheItems[$currency] = $item;
            }
        }
        $previous = null;
        if ($to = array_keys($cacheItems)) {
            try {
                $rates = $this->original->load($from, $to, $date);
            } catch (UnexpectedExchangeRateException $e) {
                $rates = $e->rates;
                $previous = $e;
            }
            foreach ($rates as $currency => $rate) {
                $result[$currency] = $rate;
                $item = $cacheItems[$currency];
                $item->set($rate);
                $this->cache->save($item);
            }
        }
        UnexpectedExchangeRateException::throwIfExistUnexpected($result, $previous);
        return $result;
    }

    public function save(string $from, DateTimeInterface $date, array $rates): void
    {
        $this->original->save($from, $date, $rates);
        foreach ($rates as $to => $rate) {
            $item = $this->cache->getItem($this->getKey($from, $to, $date));
            $item->set($rate);
            $this->cache->save($item);
        }
    }

    public function clear(): void
    {
        $this->original->clear();
        $this->cache->clear();
    }

    private function getKey(string $from, string $to, DateTimeInterface $date): string
    {
        return $from.'_'.$to.'_'.$date->format('Y-m-d');
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }
}
