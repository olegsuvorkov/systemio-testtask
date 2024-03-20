<?php

namespace App\Tests\Service\ExchangeRate\Client;

use App\Service\ExchangeRate\Client\ClientInterface;
use App\Service\ExchangeRate\Client\OpenExchangeRatesClient;
use App\Service\ExchangeRate\Exception\ExchangeRateException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[UsesClass(OpenExchangeRatesClient::class)]
class OpenExchangeRatesClientTest extends KernelTestCase
{
    private MockHttpClient $client;

    private ClientInterface $exchangeRateClient;

    protected function setUp(): void
    {
        $this->client = new MockHttpClient();
        self::getContainer()->set('open_exchange_rates.client', $this->client);
        $this->exchangeRateClient = self::getContainer()->get(OpenExchangeRatesClient::class);
    }

    #[Test]
    public function getLatestSuccess(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "disclaimer" => "Usage subject to terms: https://openexchangerates.org/terms",
            "license" => "https://openexchangerates.org/license",
            "timestamp" => 1710860400,
            "base" => "USD",
            "rates" => [
                "EUR" => 0.920608,
                "RUB" => 92.378773
            ]
        ]));
        self::assertEqualsWithDelta(
            ['RUB' => 92.378773, 'EUR' => 0.920608],
            $this->exchangeRateClient->getByDate('USD', ['EUR', 'RUB'], new DateTimeImmutable()),
            0.0001
        );
    }

    #[Test]
    public function getLatestFailure(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "error" => true,
            "status" => 403,
            "message" => "not_allowed",
            "description" => "Changing the API `base` currency is available for Developer, Enterprise and Unlimited plan clients. Please upgrade, or contact support@openexchangerates.org with any questions."
        ], [
            'http_code' => 403
        ]));
        self::expectException(ExchangeRateException::class);
        self::expectExceptionCode(403);
        self::expectExceptionMessageMatches("~^not_allowed:~");
        $this->exchangeRateClient->getByDate('USD', ['EUR', 'RUB'], new DateTimeImmutable());
    }

    #[Test]
    public function getByDateSuccess(): void
    {
        $this->client->setResponseFactory(static function (string $method, string $url, array $params) {
            self::assertEquals('GET', $method);
            self::assertStringContainsString('historical/2222-12-14.json', $url);
            self::assertArrayHasKey('query', $params);
            self::assertEquals(["base" => "USD", "symbols" => "RUB,EUR"], $params['query']);
            return new JsonMockResponse([
                "disclaimer" => "Usage subject to terms: https://openexchangerates.org/terms",
                "license" => "https://openexchangerates.org/license",
                "timestamp" => 1710806398,
                "base" => "USD",
                "rates" => [
                    "EUR" => 0.919676,
                    "RUB" => 91.743119
                ]
            ]);
        });
        self::assertEqualsWithDelta(
            ["RUB" => 91.743119, "EUR" => 0.919676],
            $this->exchangeRateClient->getByDate('USD', ['RUB', 'EUR'], new DateTimeImmutable('2222-12-14')),
            0.0001
        );
    }

    #[Test]
    public function getByDateFailure(): void
    {
        $this->client->setResponseFactory(static function (string $method, string $url, array $params) {
            self::assertEquals('GET', $method);
            self::assertStringContainsString('historical/2222-12-12.json', $url);
            self::assertArrayHasKey('query', $params);
            self::assertEquals(["base" => "USD", "symbols" => "EUR,RUB"], $params['query']);
            return new JsonMockResponse([
                "error" => true,
                "status" => 403,
                "message" => "not_allowed",
                "description" => "Changing the API `base` currency is available for Developer, Enterprise and Unlimited plan clients. Please upgrade, or contact support@openexchangerates.org with any questions."
            ]);
        });
        self::expectException(ExchangeRateException::class);
        self::expectExceptionCode(403);
        self::expectExceptionMessageMatches("~^not_allowed:~");
        $this->exchangeRateClient->getByDate('USD', ['EUR', 'RUB'], new DateTimeImmutable('2222-12-12'));
    }
}
