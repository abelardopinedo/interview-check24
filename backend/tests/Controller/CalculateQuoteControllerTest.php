<?php

namespace App\Tests\Controller;

use App\DTO\CalculateRequestDTO;
use App\Service\ProviderSearchService;
use App\Service\Provider\ProviderInterface;
use App\Entity\ProviderRequestLog;
use App\Entity\RequestLog;
use App\Service\CurrentRequestLogStore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CalculateQuoteControllerTest extends WebTestCase
{
    public function testCalculateSuccess(): void
    {
        $client = static::createClient();

        // Mock the ProviderSearchService
        $mockService = $this->createMock(ProviderSearchService::class);
        $mockService->expects($this->once())
            ->method('findAll')
            ->willReturn([
                [
                    'provider' => 'provider_b',
                    'price' => 250.0,
                    'currency' => 'EUR'
                ],
                [
                    'provider' => 'provider_a',
                    'price' => 295.0,
                    'discount_price' => 280.25,
                    'currency' => 'EUR'
                ]
            ]);

        // Inject the mock into the container
        static::getContainer()->set(ProviderSearchService::class, $mockService);

        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'driver_birthday' => '1992-02-24',
            'car_type' => 'Turismo',
            'car_use' => 'Privado'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $response);
        $this->assertEquals('provider_b', $response[0]['provider']);
        $this->assertEquals('provider_a', $response[1]['provider']);
    }

    public function testCalculateValidationErrorMissingFields(): void
    {
        $client = static::createClient();

        // Missing car_use
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'driver_birthday' => '1992-02-24',
            'car_type' => 'Turismo'
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCalculateValidationErrorInvalidValues(): void
    {
        $client = static::createClient();

        // Invalid car_type
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'driver_birthday' => '1992-02-24',
            'car_type' => 'Spaceship',
            'car_use' => 'Privado'
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testDiscountIsAppliedWhenProviderEnrolled(): void
    {
        // Mock Provider with Campaign Discount
        $providerEnrolled = $this->createStub(ProviderInterface::class);
        $providerEnrolled->method('hasCampaignDiscount')->willReturn(true);
        $providerEnrolled->method('getName')->willReturn('provider_discounted');

        $responseMock = $this->createStub(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);

        $providerEnrolled->method('getQuote')->willReturn(['response' => $responseMock, 'payload' => '{"test": "data"}']);
        $providerEnrolled->method('parseResponse')->willReturn([
            'provider' => 'provider_discounted',
            'price' => 100.0,
            'currency' => 'EUR'
        ]);
        $providerEnrolled->method('applyDiscounts')->willReturnCallback(fn($q) => array_merge($q, ['discount_price' => 95.0]));

        // Mock Provider without Campaign Discount
        $providerNotEnrolled = $this->createStub(ProviderInterface::class);
        $providerNotEnrolled->method('hasCampaignDiscount')->willReturn(false);
        $providerNotEnrolled->method('getName')->willReturn('provider_regular');

        $responseMock2 = $this->createStub(ResponseInterface::class);
        $responseMock2->method('getStatusCode')->willReturn(200);

        $providerNotEnrolled->method('getQuote')->willReturn(['response' => $responseMock2, 'payload' => '{"test": "data"}']);
        $providerNotEnrolled->method('parseResponse')->willReturn([
            'provider' => 'provider_regular',
            'price' => 100.0,
            'currency' => 'EUR'
        ]);
        $providerNotEnrolled->method('applyDiscounts')->willReturnArgument(0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService([$providerEnrolled, $providerNotEnrolled], $entityManager, $logStore);
        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');

        $results = $service->findAll($dto);

        $this->assertCount(2, $results);

        foreach ($results as $result) {
            if ($result['provider'] === 'provider_discounted') {
                $this->assertArrayHasKey('discount_price', $result);
                $this->assertEquals(95.0, $result['discount_price'], 'Discounted price should be 95% of original');
            } else {
                $this->assertArrayNotHasKey('discount_price', $result);
                $this->assertEquals(100.0, $result['price']);
            }
        }
    }

    public function testSortingByEffectivePrice(): void
    {
        // Provider A: 100 EUR, no discount
        $providerA = $this->createStub(ProviderInterface::class);
        $providerA->method('hasCampaignDiscount')->willReturn(false);
        $providerA->method('getName')->willReturn('provider_a');
        $responseMockA = $this->createStub(ResponseInterface::class);
        $responseMockA->method('getStatusCode')->willReturn(200);
        $providerA->method('getQuote')->willReturn(['response' => $responseMockA, 'payload' => '{"test": "data"}']);
        $providerA->method('parseResponse')->willReturn(['provider' => 'provider_a', 'price' => 100.0, 'currency' => 'EUR']);
        $providerA->method('applyDiscounts')->willReturnArgument(0);

        // Provider B: 102 EUR, has discount (96.9)
        $providerB = $this->createStub(ProviderInterface::class);
        $providerB->method('hasCampaignDiscount')->willReturn(true);
        $providerB->method('getName')->willReturn('provider_b');
        $responseMockB = $this->createStub(ResponseInterface::class);
        $responseMockB->method('getStatusCode')->willReturn(200);
        $providerB->method('getQuote')->willReturn(['response' => $responseMockB, 'payload' => '{"test": "data"}']);
        $providerB->method('parseResponse')->willReturn(['provider' => 'provider_b', 'price' => 102.0, 'currency' => 'EUR']);
        $providerB->method('applyDiscounts')->willReturnCallback(fn($q) => array_merge($q, ['discount_price' => 96.9]));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService([$providerA, $providerB], $entityManager, $logStore);
        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');

        $results = $service->findAll($dto);

        // If sorted correctly by effective price, provider_b should be first
        $this->assertEquals('provider_b', $results[0]['provider'], 'Provider B should be first because it is cheaper after discount');
    }

    public function testProviderFailureDoesNotBreakExecution(): void
    {
        // Mock a failing Provider (500 Internal Server Error)
        $failingProvider = $this->createStub(ProviderInterface::class);
        $failingProvider->method('getName')->willReturn('failing_provider');

        $errorResponse = $this->createStub(ResponseInterface::class);
        $errorResponse->method('getStatusCode')->willReturn(500);

        $failingProvider->method('getQuote')->willReturn(['response' => $errorResponse, 'payload' => '{"test": "data"}']);

        // Mock a successful Provider
        $successfulProvider = $this->createStub(ProviderInterface::class);
        $successfulProvider->method('getName')->willReturn('successful_provider');

        $successResponse = $this->createStub(ResponseInterface::class);
        $successResponse->method('getStatusCode')->willReturn(200);

        $successfulProvider->method('getQuote')->willReturn(['response' => $successResponse, 'payload' => '{"test": "data"}']);
        $successfulProvider->method('parseResponse')->willReturn([
            'provider' => 'successful_provider',
            'price' => 150.0,
            'currency' => 'EUR'
        ]);
        $successfulProvider->method('applyDiscounts')->willReturnArgument(0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService([$failingProvider, $successfulProvider], $entityManager, $logStore);
        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');

        $results = $service->findAll($dto);

        // Assert that the failing provider was ignored and the successful one is returned
        $this->assertCount(1, $results);
        $this->assertEquals('successful_provider', $results[0]['provider']);
    }
}
