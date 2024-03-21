<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\PurchasePayload;
use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentFactoryInterface;
use App\Service\PriceBuilder\Exception\PriceBuilderException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PaymentController extends AbstractController
{
    use PriceBuilderControllerTrait;

    #[Route('/purchase', methods: 'POST')]
    public function purchase(
        #[MapRequestPayload(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        PurchasePayload $payload,
        PaymentFactoryInterface $paymentFactory,
    ): Response
    {
        $payment = $paymentFactory->createPayment($payload->paymentProcessor);
        try {
            $this->createPriceBuilderByPayload($payload, $payment)
                ->buildPrice();
            $payment->pay();
        } catch (PriceBuilderException $e) {
            throw new BadRequestHttpException('Failed to calculate price.', previous: $e);
        } catch (PaymentException $e) {
            throw new BadRequestHttpException('Failed to pay.', previous: $e);
        }

        return $this->json([
            'message' => new TranslatableMessage('Payment was successful.'),
        ]);
    }
}
