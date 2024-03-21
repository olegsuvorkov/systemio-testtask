<?php declare(strict_types=1);

namespace App\Service\Payment\Handler;

use App\Service\Payment\PaymentInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.payment.handler')]
interface PaymentHandlerInterface
{
    public function getCurrency(): string;

    public function handle(PaymentInterface $payment): void;
}
