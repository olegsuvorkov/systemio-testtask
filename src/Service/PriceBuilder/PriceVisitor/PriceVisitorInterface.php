<?php declare(strict_types=1);

namespace App\Service\PriceBuilder\PriceVisitor;

use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.price_builder.price_visitor')]
interface PriceVisitorInterface
{
    public function getPriority(): int;

    /**
     * @param PriceBuilderInterface $priceBuilder
     * @param mixed $data
     * @throws PriceBuilderException
     */
    public function calculatePrice(PriceBuilderInterface $priceBuilder, mixed $data): void;
}
