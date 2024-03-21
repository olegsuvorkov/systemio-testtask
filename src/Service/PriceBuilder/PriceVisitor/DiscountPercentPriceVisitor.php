<?php declare(strict_types=1);

namespace App\Service\PriceBuilder\PriceVisitor;

use App\Entity\CouponPercent;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('discount_percent')]
readonly class DiscountPercentPriceVisitor implements PriceVisitorInterface
{
    public function getPriority(): int
    {
        return 300;
    }

    public function calculatePrice(PriceBuilderInterface $priceBuilder, mixed $data): void
    {
        if (!$data instanceof CouponPercent) {
            throw new PriceBuilderException();
        }
        $priceBuilder->setPrice($priceBuilder->getPrice() * (100.0 - $data->percent) / 100.0);
    }
}