<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Client;

use App\Service\ExchangeRate\Exception\ExchangeRateException;
use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsAlias]
readonly class FixerClient implements ClientInterface
{
    private const array SCHEMA = [
        'type' => 'object',
        'oneOf' => [
            [
                'type' => 'object',
                'properties' => [
                    'rates' => [
                        'type' => 'object',
                        'patternProperties' => [
                            '^[A-Z]{3}$' => ['type' => 'number', 'minimum' => 0],
                        ],
                        'additionalProperties' => false,
                        'minProperties' => 1,
                    ],
                ],
                'additionalProperties' => true,
                'required' => ['rates'],
            ],
            [
                'type' => 'object',
                'properties' => [
                    'error' => [
                        'type' => "object",
                        'properties' => [
                            'code' => ['type' => 'integer'],
                            'type' => ['type' => 'string'],
                            'info' => ['type' => 'string'],
                        ],
                        'additionalProperties' => true,
                        'required' => ['code'],
                    ],
                ],
                'additionalItems' => true,
                'required' => ['error'],
            ],
        ],
    ];

    public function __construct(
        #[Target('api_layer.client')]
        private HttpClientInterface $client,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getByDate(string $from, array $to, DateTimeInterface $date): array
    {
        $result = array_fill_keys($to, null);
        $path = $this->isToday($date) ? 'latest' : $date->format('Y-m-d');
        try {
            $response = $this->client->request('GET', $path, ['query' => [
                'base' => $from,
                'symbols' => implode(',', $to),
            ]]);
            dump($response->getContent(false));
            $data = $this->fetchAndValidate($response, self::SCHEMA);
            $rates = array_map(floatval(...), $data['rates']);
            $rates = array_intersect_key($rates, $result);
            $result = [...$result, ...$rates];
        } catch (ExceptionInterface $e) {
            throw new ExchangeRateException(previous: $e);
        }
        UnexpectedExchangeRateException::throwIfExistUnexpected($result);
        return $result;
    }

    private function isToday(DateTimeInterface $date): bool
    {
        $now = (new DateTimeImmutable('today', new DateTimeZone('+00:00')));
        return $date->format('Y-m-d') === $now->format('Y-m-d');
    }

    /**
     * @param ResponseInterface $response
     * @param array $schema
     * @return array
     * @throws ExchangeRateException
     * @throws ExceptionInterface
     */
    private function fetchAndValidate(ResponseInterface $response, array $schema): array
    {
        $validator = new Validator();
        $data = $response->toArray();
        if ($validator->validate($data, $schema, Constraint::CHECK_MODE_TYPE_CAST)) {
            throw new ExchangeRateException(
                json_encode($validator->getErrors()),
                previous: new ClientException($response),
            );
        }
        if ($error = $data['error'] ?? []) {
            throw new ExchangeRateException(
                sprintf('%s: %s', $error['type'] ?? '', $error['info'] ?? ''), $error['code'],
                new ClientException($response)
            );
        }
        return $data;
    }
}
