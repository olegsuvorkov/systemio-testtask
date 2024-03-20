<?php

namespace App\Tests\Service\ExchangeRate\Client;

use App\Service\ExchangeRate\Client\ClientInterface;
use App\Service\ExchangeRate\Client\FixerClient;
use App\Service\ExchangeRate\Exception\ExchangeRateException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[UsesClass(FixerClient::class)]
class FixerClientTest extends KernelTestCase
{
    private MockHttpClient $client;

    private ClientInterface $exchangeRateClient;

    protected function setUp(): void
    {
        $this->client = new MockHttpClient();
        self::getContainer()->set('api_layer.client', $this->client);
        $this->exchangeRateClient = self::getContainer()->get(FixerClient::class);
    }

    #[Test]
    public function getLatestSuccess(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "success" => true,
            "timestamp" => 1710349324,
            "base" => "EUR",
            "date" => "2024-03-13",
            "rates" => [
                "USD" => 1.095524,
                "RUB" => 100.497884,
            ],
        ]));
        self::assertEqualsWithDelta(
            ["USD" => 1.095524, "RUB" => 100.497884],
            $this->exchangeRateClient->getByDate('EUR', ['USD', 'RUB'], new DateTimeImmutable()),
            0.0001
        );
    }

    #[Test]
    public function getLatestFailure(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "success" => false,
            "error" => [
                "code" => 101,
                "type" => "invalid_access_key",
                "info" => "You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"
            ]
        ]));
        self::expectException(ExchangeRateException::class);
        self::expectExceptionCode(101);
        self::expectExceptionMessage("You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]");
        $this->exchangeRateClient->getByDate('EUR', ['USD', 'RUB'], new DateTimeImmutable());
    }

    #[Test]
    public function getByDateSuccess(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "success" => true,
            "timestamp" => 1710349324,
            "historical" => true,
            "base" => "EUR",
            "date" => "2024-03-13",
            "rates" => [
                "USD" => 1.095524,
                "RUB" => 100.497884,
            ],
        ]));
        self::assertEqualsWithDelta(
            ["USD" => 1.095524, "RUB" => 100.497884],
            $this->exchangeRateClient->getByDate('EUR', ['USD', 'RUB'], new DateTimeImmutable()),
            0.0001
        );
    }

    #[Test]
    public function getByDateFailure(): void
    {
        $this->client->setResponseFactory(new JsonMockResponse([
            "success" => false,
            "error" => [
                "code" => 302,
                "type" => "invalid_date",
                "info" => "You have entered an invalid date. [Required format: date=YYYY-MM-DD]"
            ]
        ]));
        self::expectException(ExchangeRateException::class);
        self::expectExceptionCode(302);
        self::expectExceptionMessage("You have entered an invalid date. [Required format: date=YYYY-MM-DD]");
        $this->exchangeRateClient->getByDate('EUR', ['USD', 'RUB'], new DateTimeImmutable('2222-12-12'));
    }
}
