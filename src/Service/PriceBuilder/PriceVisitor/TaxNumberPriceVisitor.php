<?php declare(strict_types=1);

namespace App\Service\PriceBuilder\PriceVisitor;

use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderInterface;
use App\Service\Tax\Exception\InvalidTaxNumberException;
use App\Service\Tax\Exception\UnknownTaxFormatException;
use App\Service\Tax\TaxRule\TaxRuleInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('tax')]
readonly class TaxNumberPriceVisitor implements PriceVisitorInterface
{
    public function __construct(
        private TaxRuleInterface $taxRule,
    )
    {
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function calculatePrice(PriceBuilderInterface $priceBuilder, mixed $data): void
    {
        try {
            $priceBuilder->setPrice($this->taxRule->calculatePrice($data, $priceBuilder->getPrice()));
        } catch (InvalidTaxNumberException|UnknownTaxFormatException $e) {
            throw new PriceBuilderException(previous: $e);
        }
    }
}
