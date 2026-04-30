<?php

namespace App\Tests\Service;

use App\DTO\CalculateRequestDTO;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use App\Service\Provider\ProviderA;
use App\Service\Provider\ProviderB;
use App\Service\ProviderSearchService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProviderSearchServiceTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder(), new JsonEncoder()]);
    }

    private function createMockProvider(string $class, $client, string $url, bool $hasDiscount)
    {
        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())->setUrl($url)->setHasDiscount($hasDiscount);
        $repository->method('findOneByName')->willReturn($providerEntity);

        return new $class($client, $repository, $this->serializer);
    }

    public function testFindAllSortsAndAppliesDiscounts(): void
    {
        // Provider A: Base 295, has discount -> returns 280.25
        $mockResponseA = new MockResponse(json_encode(['price' => '295 EUR']));
        $clientA = new MockHttpClient($mockResponseA);
        $providerA = $this->createMockProvider(ProviderA::class, $clientA, 'http://test.local', true);

        // Provider B: Base 250, no discount -> returns 250
        $xmlResponseB = <<<XML
        <RespuestaCotizacion><Precio>250.0</Precio><Moneda>EUR</Moneda></RespuestaCotizacion>
        XML;
        $mockResponseB = new MockResponse($xmlResponseB);
        $clientB = new MockHttpClient($mockResponseB);
        $providerB = $this->createMockProvider(ProviderB::class, $clientB, 'http://test.local', false);

        $service = new ProviderSearchService([$providerA, $providerB]);

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
        // Provider A throws exception (e.g. timeout)
        $clientA = new MockHttpClient(new MockResponse('', ['error' => 'Timeout']));
        $providerA = $this->createMockProvider(ProviderA::class, $clientA, 'http://test.local', false);

        // Provider B succeeds (310 EUR)
        $xmlResponseB = <<<XML
        <RespuestaCotizacion><Precio>310.0</Precio><Moneda>EUR</Moneda></RespuestaCotizacion>
        XML;
        $mockResponseB = new MockResponse($xmlResponseB);
        $clientB = new MockHttpClient($mockResponseB);
        $providerB = $this->createMockProvider(ProviderB::class, $clientB, 'http://test.local', false);

        $service = new ProviderSearchService([$providerA, $providerB]);

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $results = $service->findAll($dto);

        // Should return only Provider B gracefully
        $this->assertCount(1, $results);
        $this->assertEquals('provider_b', $results[0]['provider']);
        $this->assertEquals(310.0, $results[0]['price']);
    }

    public function testFindAllHandlesTotalFailure(): void
    {
        // Both providers fail
        $clientA = new MockHttpClient(new MockResponse('', ['error' => 'Timeout']));
        $providerA = $this->createMockProvider(ProviderA::class, $clientA, 'http://test.local', false);

        $clientB = new MockHttpClient(new MockResponse('', ['error' => '500 Error']));
        $providerB = $this->createMockProvider(ProviderB::class, $clientB, 'http://test.local', false);

        $service = new ProviderSearchService([$providerA, $providerB]);

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $results = $service->findAll($dto);

        // Should return empty array
        $this->assertCount(0, $results);
        $this->assertIsArray($results);
    }
}
