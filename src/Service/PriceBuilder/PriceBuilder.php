<?php declare(strict_types=1);

namespace App\Service\PriceBuilder;

use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceHandler\PriceHandler;
use App\Service\PriceBuilder\PriceHandler\PriceHandlerFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Throwable;

#[Exclude]
class PriceBuilder implements PriceBuilderInterface
{
    /**
     * @var PriceHandler[]
     */
    private array $rules = [];

    public function __construct(
        private readonly PriceHandlerFactoryInterface $priceHandlerFactory,
        private readonly CouponProviderInterface      $couponProvider,
        private readonly ProductProviderInterface     $productProvider,
        private readonly PriceInterface               $price,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function addProduct(int $productId): self
    {
        try {
            $product = $this->productProvider->getProduct($productId);
            $this->rules[] = $this->priceHandlerFactory->createPriceHandler('product', $product);
        } catch (Throwable $e) {
            throw new PriceBuilderException(previous: $e);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addCouponCode(string $couponCode): self
    {
        try {
            $coupon = $this->couponProvider->getCoupon($couponCode);
            $this->rules[] = $this->priceHandlerFactory->createPriceHandler($coupon->getType(), $coupon);
        } catch (Throwable $e) {
            throw new PriceBuilderException(previous: $e);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTaxNumber(string $taxNumber): self
    {
        $this->rules['tax'] = $this->priceHandlerFactory->createPriceHandler('tax', $taxNumber);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function buildPrice(): self
    {
        $this->setPrice(0.0);
        $rules = $this->rules;
        usort($rules, static function (PriceHandler $left, PriceHandler $right) {
            return $right->getPriority() <=> $left->getPriority();
        });
        foreach ($rules as $rule) {
            $rule->handle($this);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): float
    {
        return $this->price->getPrice();
    }

    /**
     * @inheritDoc
     */
    public function setPrice(float $price): self
    {
        $this->price->setPrice($price);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return $this->price->getCurrency();
    }
}
