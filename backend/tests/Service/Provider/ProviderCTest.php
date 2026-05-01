<?php

namespace App\Tests\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use App\Service\Provider\ProviderC;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProviderCTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function testGetQuoteMapping(): void
    {
        $mockResponse = new MockResponse(json_encode(['payload' => ['price' => '325 EUR']]));
        $client = new MockHttpClient($mockResponse);

        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())
            ->setName('Provider C')
            ->setUrl('http://test.local/provider-c/quote')
            ->setHasDiscount(false);
        $repository->method('findOneBy')->willReturn($providerEntity);

        $provider = new ProviderC($client, $repository, $this->serializer);

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $provider->getQuote($dto);

        $requestOptions = $mockResponse->getRequestOptions();

        $this->assertArrayHasKey('body', $requestOptions);

        $body = json_decode($requestOptions['body'], true);

        $this->assertArrayHasKey('driverInfo', $body);
        $this->assertArrayHasKey('carInfo', $body);
        $this->assertEquals('compact', $body['carInfo']['car_form']);
        $this->assertEquals('private', $body['carInfo']['car_use']);
        $this->assertIsInt($body['driverInfo']['age']);
    }

    public function testParseResponse(): void
    {
        $mockResponse = new MockResponse(json_encode(['payload' => ['price' => '500.00 EUR']]));
        $client = new MockHttpClient($mockResponse);

        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())
            ->setName('Provider C')
            ->setUrl('http://test.local')
            ->setHasDiscount(false);
        $repository->method('findOneBy')->willReturn($providerEntity);

        $provider = new ProviderC($client, $repository, $this->serializer);

        // Simulating the call to get the response object
        $response = $client->request('POST', 'http://test.local');

        $parsed = $provider->parseResponse($response);

        $this->assertEquals('Provider C', $parsed['provider']);
        $this->assertEquals(500.0, $parsed['price']);
        $this->assertEquals('EUR', $parsed['currency']);
    }
}
