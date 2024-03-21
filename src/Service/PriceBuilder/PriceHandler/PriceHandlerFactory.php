<?php declare(strict_types=1);

namespace App\Service\PriceBuilder\PriceHandler;

use App\Service\PriceBuilder\PriceVisitor\PriceVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

readonly class PriceHandlerFactory implements PriceHandlerFactoryInterface
{
    /**
     * @param ServiceProviderInterface<PriceVisitorInterface> $locator
     */
    public function __construct(
        #[TaggedLocator('service.price_builder.price_visitor')]
        private ServiceProviderInterface $locator,
    )
    {
    }

    public function createPriceHandler(string $name, mixed $data): PriceHandler
    {
        $priceVisitor = $this->locator->get($name);
        return new PriceHandler($priceVisitor, $data);
    }
}