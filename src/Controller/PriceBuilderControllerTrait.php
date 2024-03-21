<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePricePayload;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use App\Service\PriceBuilder\PriceBuilderFactoryInterface;
use App\Service\PriceBuilder\PriceBuilderInterface;
use App\Service\PriceBuilder\PriceInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait PriceBuilderControllerTrait
{
    #[Required]
    public PriceBuilderFactoryInterface $priceBuilderFactory;

    /**
     * @param CalculatePricePayload $payload
     * @return PriceBuilderInterface
     * @throws PriceBuilderException
     */
    public function createPriceBuilderByPayload(CalculatePricePayload $payload, PriceInterface $price): PriceBuilderInterface
    {
        $priceBuilder = $this->priceBuilderFactory->createPriceBuilder($price);
        $priceBuilder->addProduct($payload->product);
        if ($payload->taxNumber) {
            $priceBuilder->setTaxNumber($payload->taxNumber);
        }
        if ($payload->couponCode) {
            $priceBuilder->addCouponCode($payload->couponCode);
        }
        return $priceBuilder;
    }
}
