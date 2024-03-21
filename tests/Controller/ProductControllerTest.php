<?php

namespace App\Tests\Controller;

use App\Entity\CouponPercent;
use App\Entity\Product;
use App\Entity\Tax;
use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Handler\PaymentHandlerInterface;
use App\Service\Payment\Handler\PaypalPaymentHandler;
use App\Service\Payment\PaymentInterface;
use Doctrine\DBAL\Connection;
use Helmich\JsonAssert\Constraint\JsonValueMatchesSchema;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use function Symfony\Component\String\s;

class ProductControllerTest extends WebTestCase
{
    private PaypalPaymentProcessor & MockObject $processor;

    private PaymentHandlerInterface $handler;

    private PaymentInterface $payment;

    private static int $product1Id;
    private static int $product2Id;

    private static string $coupon = 'D1';

    public function setUp(): void
    {
        self::clearTable();
        self::createClient();
        /** @var ManagerRegistry $register */
        $register = self::getContainer()->get('doctrine');
        /** @var Connection $connection */
        $manager = $register->getManager();

        $product1 = new Product();
        $product1->price = 100.0;
        $product1->currency = 'EUR';
        $manager->persist($product1);
        $product2 = new Product();
        $product2->price = 10.0;
        $product2->currency = 'EUR';
        $manager->persist($product2);

        $tax = new Tax();
        $tax->format = 'XXXX';
        $tax->percent = 10.0;
        $tax->countryCode = 'FR';
        $manager->persist($tax);

        $coupon = new CouponPercent(self::$coupon);
        $coupon->percent = 15.0;
        $manager->persist($coupon);
        $manager->flush();

        self::$product1Id = $product1->id;
        self::$product2Id = $product2->id;
        self::ensureKernelShutdown();
    }

    #[After]
    public static function clearTable(): void
    {
        /** @var ManagerRegistry $register */
        $register = self::getContainer()->get('doctrine');
        /** @var Connection $connection */
        $manager = $register->getManager();
        $connection = $manager->getConnection();
        $connection->executeStatement('DELETE FROM "product"');
        $connection->executeStatement('DELETE FROM "coupon"');
        $connection->executeStatement('DELETE FROM "tax"');
        self::ensureKernelShutdown();
    }

    #[Test]
    public function calculatePriceSuccess(): void
    {
        self::getClient()
            ->jsonRequest('POST', '/calculate-price', [
                'product' => self::$product1Id,
                'taxNumber' => 'FR1234',
                'couponCode' => self::$coupon,
            ]);
        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        $content = self::getClient()->getResponse()->getContent();
        self::assertThat($content, new JsonValueMatchesSchema([
            'type' => 'object',
            'properties' => [
                'price' => [
                    'type' => 'number',
                ],
                'currency' => [
                    'type' => 'string',
                ]
            ]
        ]));
    }

    #[Test]
    public function calculatePriceFailure(): void
    {
        self::getClient()
            ->jsonRequest('POST', '/calculate-price', [
                'product' => self::$product1Id+100,
                'couponCode' => self::$coupon.'2',
            ]);
        self::assertResponseStatusCodeSame(400);
        self::assertResponseFormatSame('json');
        $content = self::getClient()->getResponse()->getContent();
        self::assertThat($content, new JsonValueMatchesSchema([
            [
                "type" => "object",
                "properties" => [
                    "title" => [
                        "type" => "string"
                    ],
                    "errors" => [
                        "type" => "array",
                        "items" => [
                            "type" => "object",
                            "properties" => [
                                "propertyPath" => [
                                    "type" => "string"
                                ]
                            ],
                            "required" => [
                                "propertyPath",
                            ],
                            'additionalProperties' => true,
                        ]
                    ]
                ],
                "required" => [
                    "title",
                    "errors"
                ],
                'additionalProperties' => true,
            ]
        ]));
        $data = json_decode($content, true);
        self::assertEquals('Ошибка валидации', $data['title']);
        $propertyPathList = array_column($data['errors'], 'propertyPath');
        self::assertContains('couponCode', $propertyPathList);
        self::assertContains('product', $propertyPathList);
        self::assertContains('taxNumber', $propertyPathList);
    }
}
