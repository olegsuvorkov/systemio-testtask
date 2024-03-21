<?php declare(strict_types=1);

namespace App\Service\Payment\Handler;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

#[AsTaggedItem('paypal')]
readonly class PaypalPaymentHandler implements PaymentHandlerInterface
{
    public function __construct(
        #[Autowire(service: 'systemeio.payment_processor.paypal')]
        private PaypalPaymentProcessor $processor,
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
            $this->processor->pay((int) ($payment->getPrice() * 100));
        } catch (Exception $e) {
            throw new PaymentException(previous: $e);
        }
    }
}