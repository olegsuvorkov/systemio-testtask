<?php declare(strict_types=1);

namespace App\Service\Payment;

interface PaymentFactoryInterface
{
    public function createPayment(string $name): PaymentInterface;
}