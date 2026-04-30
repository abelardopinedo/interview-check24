<?php

namespace App\Tests\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use App\Service\Provider\ProviderA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProviderATest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }
    public function testGetQuoteMapping(): void
    {
        $mockResponse = new MockResponse(json_encode(['price' => '295 EUR']));
        $client = new MockHttpClient($mockResponse);

        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())->setUrl('http://test.local/provider-a/quote')->setHasDiscount(true);
        $repository->method('findOneByName')->willReturn($providerEntity);

        $provider = new ProviderA($client, $repository, $this->serializer);

        $dto = new CalculateRequestDTO('1990-01-01', 'Turismo', 'Privado');
        $provider->getQuote($dto);

        $requestOptions = $mockResponse->getRequestOptions();

        $this->assertArrayHasKey('body', $requestOptions);

        $body = json_decode($requestOptions['body'], true);

        $this->assertEquals('compact', $body['car_form']);
        $this->assertEquals('private', $body['car_use']);
        // age is calculated dynamically from birthdate to today. Let's just assert it exists and is an int.
        $this->assertIsInt($body['driver_age']);
    }

    public function testParseResponse(): void
    {
        $mockResponse = new MockResponse(json_encode(['price' => '315.5 EUR']));
        $client = new MockHttpClient($mockResponse);

        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())->setUrl('http://test.local')->setHasDiscount(false);
        $repository->method('findOneByName')->willReturn($providerEntity);

        $provider = new ProviderA($client, $repository, $this->serializer);

        // Simulating the call to get the response object
        $response = $client->request('POST', 'http://test.local');

        $parsed = $provider->parseResponse($response);

        $this->assertEquals('provider_a', $parsed['provider']);
        $this->assertEquals(315.5, $parsed['price']);
        $this->assertEquals('EUR', $parsed['currency']);
    }
}
