<?php declare(strict_types=1);

namespace App\DTO;

use App\Validator\PaymentHandler;
use Symfony\Component\Validator\Constraints as Assert;

class PurchasePayload extends CalculatePricePayload
{
    #[Assert\NotBlank]
    #[PaymentHandler]
    public string $paymentProcessor;
}
