<?php

namespace App\Tests\Service\Payment\Handler;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Handler\PaymentHandlerInterface;
use App\Service\Payment\Handler\StripePaymentHandler;
use App\Service\Payment\PaymentInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentHandlerTest extends KernelTestCase
{
    private StripePaymentProcessor & MockObject $processor;

    private PaymentHandlerInterface $handler;

    private PaymentInterface $payment;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->processor = self::createMock(StripePaymentProcessor::class);
        $container->set('systemeio.payment_processor.stripe', $this->processor);
        $this->handler = $container->get(StripePaymentHandler::class);
        $this->payment = self::createMock(PaymentInterface::class);
        $this->payment
            ->expects(self::once())
            ->method('getPrice')
            ->willReturn(10.0);

    }

    #[Test]
    public function handleSuccess(): void
    {
        $this->processor
            ->expects(self::once())
            ->method('processPayment')
            ->with(10.0)
            ->willReturn(true);

        $this->handler->handle($this->payment);
    }

    #[Test]
    public function handleFailure(): void
    {
        $this->processor
            ->expects(self::once())
            ->method('processPayment')
            ->with(10.0)
            ->willReturn(false);
        self::expectException(PaymentException::class);

        $this->handler->handle($this->payment);
    }
}
