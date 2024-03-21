<?php declare(strict_types=1);

namespace App\Service\Payment\Handler;

use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends ServiceProviderInterface<PaymentHandlerInterface>
 */
readonly class PaymentHandlerProvider implements ServiceProviderInterface
{
    public function __construct(
        #[TaggedLocator('service.payment.handler')]
        private ServiceProviderInterface $original,
    )
    {
    }

    public function get(string $id): mixed
    {
        return $this->original->get($id);
    }

    public function has(string $id): bool
    {
        return $this->original->has($id);
    }

    public function getProvidedServices(): array
    {
        return $this->original->getProvidedServices();
    }
}