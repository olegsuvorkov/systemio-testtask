<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\Payment\Handler\PaymentHandlerProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PaymentHandlerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider,
    )
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var PaymentHandler $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        if (!$this->paymentHandlerProvider->has($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
