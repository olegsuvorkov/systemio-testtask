<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Client;

use ArrayIterator;
use IteratorAggregate;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Traversable;

/**
 * @extends IteratorAggregate<int, ClientInterface>
 */
class ClientCollection implements IteratorAggregate
{
    private Traversable $iterator;

    public function __construct(
        #[AutowireIterator('service.exchange_rate.client')]
        iterable $list,
    )
    {
        $this->iterator = $list instanceof Traversable ? $list : new ArrayIterator($list);
    }

    public function add(ClientInterface $client)
    {
        $this->iterator = new ArrayIterator([...iterator_to_array($this->iterator), $client]);
    }

    public function getIterator(): Traversable
    {
        return $this->iterator;
    }
}
