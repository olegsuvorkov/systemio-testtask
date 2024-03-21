<?php

namespace App\Service\Tax\TaxRule;

use App\Service\Tax\Exception\InvalidTaxNumberException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsAlias(TaxRuleInterface::class)]
readonly class ChainTaxRule implements TaxRuleInterface
{
    /**
     * @param TaxRuleInterface[] $rules
     */
    public function __construct(
        #[TaggedIterator('service.tax.tax_rule', excludeSelf: true)]
        private iterable $rules,
    )
    {
    }

    public function checkNumber(string $number): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->checkNumber($number)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function calculatePrice(string $number, float $price): float
    {
        foreach ($this->rules as $rule) {
            if ($rule->checkNumber($number)) {
                return $rule->calculatePrice($number, $price);
            }
        }
        throw new InvalidTaxNumberException();
    }
}