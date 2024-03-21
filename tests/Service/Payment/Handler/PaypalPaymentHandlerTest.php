<?php

namespace App\Tests\Service\Payment\Handler;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Handler\PaymentHandlerInterface;
use App\Service\Payment\Handler\PaypalPaymentHandler;
use App\Service\Payment\PaymentInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentHandlerTest extends KernelTestCase
{
    private PaypalPaymentProcessor & MockObject $processor;

    private PaymentHandlerInterface $handler;

    private PaymentInterface $payment;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->processor = self::createMock(PaypalPaymentProcessor::class);
        $container->set('systemeio.payment_processor.paypal', $this->processor);
        $this->handler = $container->get(PaypalPaymentHandler::class);
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
            ->method('pay')
            ->with(1000);

        $this->handler->handle($this->payment);
    }

    #[Test]
    public function handleFailure(): void
    {
        $this->processor
            ->expects(self::once())
            ->method('pay')
            ->with(1000)
            ->willThrowException(new \Exception());
        self::expectException(PaymentException::class);

        $this->handler->handle($this->payment);
    }
}
