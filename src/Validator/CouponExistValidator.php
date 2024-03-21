<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\PriceBuilder\CouponProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Throwable;

class CouponExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CouponProviderInterface $couponProvider,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }
        /* @var CouponExist $constraint */
        try {
            $this->couponProvider->getCoupon($value);
        } catch (Throwable) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
