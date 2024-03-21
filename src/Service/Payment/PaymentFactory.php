<?php declare(strict_types=1);

namespace App\Service\Payment;

use App\Service\Payment\Handler\PaymentHandlerInterface;
use App\Service\Payment\Handler\PaymentHandlerProvider;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

#[AsAlias]
readonly class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @param ServiceProviderInterface<PaymentHandlerInterface> $locator
     */
    public function __construct(
        private PaymentHandlerProvider $locator,
    )
    {
    }

    public function createPayment(string $name): PaymentInterface
    {
        $handler = $this->locator->get($name);
        return new Payment($handler);
    }
}
