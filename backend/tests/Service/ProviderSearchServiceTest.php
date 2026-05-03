<?php

namespace App\Tests\Service;

use App\DTO\CalculateRequestDTO;
use App\Entity\Provider;
use App\Service\ProviderSearchService;
use App\Service\Provider\ProviderInterface;
use App\Entity\ProviderRequestLog;
use App\Entity\RequestLog;
use App\Service\CurrentRequestLogStore;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ProviderSearchServiceTest extends TestCase
{
    private function createMockProvider(string $name, float $price, bool $hasDiscount, int $statusCode = 200)
    {
        $provider = $this->createStub(ProviderInterface::class);
        $provider->method('getName')->willReturn($name);
        $provider->method('hasCampaignDiscount')->willReturn($hasDiscount);
        $provider->method('getUrl')->willReturn('http://test.local');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);

        $provider->method('getQuote')->willReturn(['response' => $response, 'payload' => '{"test": "data"}']);
        $provider->method('parseResponse')->willReturn([
            'provider' => $name,
            'price' => $price,
            'currency' => 'EUR'
        ]);

        $providerEntity = new Provider();
        $providerEntity->setName($name);
        $providerEntity->setUrl('http://test.local');
        $providerEntity->setInternalKey('key_' . $name);
        $providerEntity->setHasDiscount($hasDiscount);

        $provider->method('getProviderEntity')->willReturn($providerEntity);
        $provider->method('applyDiscounts')->willReturnCallback(function ($quote) use ($hasDiscount) {
            if ($hasDiscount) {
                $quote['discount_price'] = round($quote['price'] * 0.95, 2);
            }
            return $quote;
        });

        return $provider;
    }

    public function testFindAllSortsAndAppliesDiscounts(): void
    {
        $providerA = $this->createMockProvider('provider_a', 295.0, true);
        $providerB = $this->createMockProvider('provider_b', 250.0, false);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService(
            [$providerA, $providerB],
            $entityManager,
            $logStore
        );

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $results = $service->findAll($dto);

        // Expected sorted: Provider B (250), Provider A (280.25)
        $this->assertCount(2, $results);

        $this->assertEquals('provider_b', $results[0]['provider']);
        $this->assertEquals(250.0, $results[0]['price']);
        $this->assertArrayNotHasKey('discount_price', $results[0]);

        $this->assertEquals('provider_a', $results[1]['provider']);
        $this->assertEquals(295.0, $results[1]['price']);
        $this->assertEquals(280.25, $results[1]['discount_price']);
    }

    public function testFindAllHandlesPartialFailure(): void
    {
        $providerA = $this->createMockProvider('provider_a', 300.0, false, 500);
        $providerB = $this->createMockProvider('provider_b', 310.0, false, 200);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService(
            [$providerA, $providerB],
            $entityManager,
            $logStore
        );

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $results = $service->findAll($dto);

        // Should return only Provider B gracefully
        $this->assertCount(1, $results);
        $this->assertEquals('provider_b', $results[0]['provider']);
        $this->assertEquals(310.0, $results[0]['price']);
    }

    public function testFindAllHandlesTotalFailure(): void
    {
        $providerA = $this->createMockProvider('provider_a', 0, false, 500);
        $providerB = $this->createMockProvider('provider_b', 0, false, 500);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(ProviderRequestLog::class));

        $logStore = $this->createStub(CurrentRequestLogStore::class);
        $logStore->method('getRequestLog')->willReturn($this->createStub(RequestLog::class));

        $service = new ProviderSearchService(
            [$providerA, $providerB],
            $entityManager,
            $logStore
        );

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $results = $service->findAll($dto);

        // Should return empty array
        $this->assertCount(0, $results);
    }
}
