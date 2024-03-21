<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePricePayload;
use App\DTO\Price;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    use PriceBuilderControllerTrait;

    #[Route('/calculate-price', methods: 'POST')]
    public function calculatePrice(
        #[MapRequestPayload(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CalculatePricePayload $payload,
    ): Response
    {
        $price = new Price('EUR');
        try {
            $this->createPriceBuilderByPayload($payload, $price)
                ->buildPrice();
        } catch (PriceBuilderException $e) {
            throw new BadRequestHttpException('Failed to calculate price.', previous: $e);
        }

        return $this->json($price);
    }
}
