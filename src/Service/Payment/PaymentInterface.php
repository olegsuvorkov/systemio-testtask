<?php declare(strict_types=1);

namespace App\Service\Payment;

use App\Service\Payment\Exception\PaymentException;
use App\Service\PriceBuilder\PriceInterface;

interface PaymentInterface extends PriceInterface
{
    /**
     * @throws PaymentException
     */
    public function pay(): void;
}
