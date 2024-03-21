<?php declare(strict_types=1);

namespace App\Service\Payment\Handler;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

#[AsTaggedItem('stripe')]
readonly class StripePaymentHandler implements PaymentHandlerInterface
{
    public function __construct(
        #[Autowire(service: 'systemeio.payment_processor.stripe')]
        private StripePaymentProcessor $processor,
    )
    {
    }

    public function getCurrency(): string
    {
        return 'USD';
    }

    public function handle(PaymentInterface $payment): void
    {
        try {
            if (!$this->processor->processPayment($payment->getPrice())) {
                throw new PaymentException();
            }
        } catch (Exception $e) {
            throw new PaymentException(previous: $e);
        }
    }
}