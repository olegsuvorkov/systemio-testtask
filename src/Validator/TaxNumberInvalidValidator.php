<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\Tax\TaxRule\TaxRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TaxNumberInvalidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TaxRuleInterface $taxRule,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var TaxNumberInvalid $constraint */
        if (null === $value || $value === '') {
            return;
        }
        if (!$this->taxRule->checkNumber($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
