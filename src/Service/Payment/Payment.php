<?php declare(strict_types=1);

namespace App\Service\Payment;

use App\Service\Payment\Handler\PaymentHandlerInterface;

class Payment implements PaymentInterface
{
    private float $price = 0.0;

    public function __construct(
        private readonly PaymentHandlerInterface $handler,
    )
    {
    }

    public function getCurrency(): string
    {
        return $this->handler->getCurrency();
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @inheritDoc
     */
    public function pay(): void
    {
        $this->handler->handle($this);
    }
}