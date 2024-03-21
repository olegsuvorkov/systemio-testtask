<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer as OriginalProblemNormalizer;
use Symfony\Component\Messenger\Exception\ValidationFailedException as MessageValidationFailedException;
use Throwable;

/**
 * Переопределяем для локализации сообщения об ошибках
 */
readonly class ProblemNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.problem')]
        private NormalizerInterface $normalizer,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @inheritDoc
     * @param FlattenException $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $exception = $context['exception'] ?? null;
        if ($exception instanceof HttpExceptionInterface) {
            $exception = $exception->getPrevious();
            if ($exception instanceof PartialDenormalizationException ||
                $exception instanceof ValidationFailedException ||
                $exception instanceof MessageValidationFailedException
            ) {
                $data[OriginalProblemNormalizer::TITLE] = $this->translator->trans('Validation Failed');
                $data = [
                    OriginalProblemNormalizer::TITLE => $this->translator->trans('Validation Failed'),
                    OriginalProblemNormalizer::STATUS => $data[OriginalProblemNormalizer::STATUS],
                    'errors' => $data['violations'],
                    ...$data,
                ];
                unset($data['violations']);
                unset($data['detail']);
                $exception = null;
            }
        }
        if ($exception instanceof Throwable) {
            if ($message = $exception->getMessage() ?: $object->getMessage()) {
                $data[OriginalProblemNormalizer::TITLE] = $this->translator->trans($message);
            } elseif ($title = $data[OriginalProblemNormalizer::TITLE] ?? null) {
                $data[OriginalProblemNormalizer::TITLE] = $this->translator->trans($title);
            }
            unset($data['detail']);
        }
        unset($data[OriginalProblemNormalizer::TYPE]);
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->normalizer->getSupportedTypes($format);
    }
}
