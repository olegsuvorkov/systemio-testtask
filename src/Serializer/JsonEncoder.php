<?php declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * Переопределяем для отображения кириллицы
 */
readonly class JsonEncoder implements EncoderInterface, DecoderInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.encoder.json')]
        private EncoderInterface & DecoderInterface $original,
    )
    {
    }

    public function encode($data, string $format, array $context = []): string
    {
        $options = $context[JsonEncode::OPTIONS] ?? JsonResponse::DEFAULT_ENCODING_OPTIONS;
        $context[JsonEncode::OPTIONS] = $options | JSON_UNESCAPED_UNICODE;
        return $this->original->encode($data, $format, $context);
    }

    public function supportsEncoding(string $format, array $context = []): bool
    {
        return $this->original->supportsEncoding($format, $context);
    }

    public function decode(string $data, string $format, array $context = [])
    {
        return $this->original->decode($data, $format, $context);
    }

    public function supportsDecoding(string $format, array $context = []): bool
    {
        return $this->original->supportsDecoding($format, $context);
    }
}
