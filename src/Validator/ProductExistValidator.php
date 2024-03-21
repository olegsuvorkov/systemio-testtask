<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\PriceBuilder\ProductProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Throwable;

class ProductExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductProviderInterface $productProvider,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ProductExist $constraint */
        if (null === $value || '' === $value) {
            return;
        }
        try {
            $this->productProvider->getProduct($value);
        } catch (Throwable) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', self::formatValue($value))
                ->addViolation();
        }
    }
}
