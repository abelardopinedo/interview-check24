<?php

namespace App\Tests\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use App\Service\Provider\ProviderB;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProviderBTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = [new XmlEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testGetQuoteMapping(): void
    {
        $mockResponse = new MockResponse('<RespuestaCotizacion><Precio>310.0</Precio><Moneda>EUR</Moneda></RespuestaCotizacion>');
        $client = new MockHttpClient($mockResponse);
        
        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())->setUrl('http://test.local/provider-b/quote')->setHasDiscount(false);
        $repository->method('findOneByName')->willReturn($providerEntity);

        $provider = new ProviderB($client, $repository, $this->serializer);
        
        $dto = new CalculateRequestDTO('1990-01-01', 'SUV', 'Comercial');
        $provider->getQuote($dto);

        $requestOptions = $mockResponse->getRequestOptions();
        
        $this->assertArrayHasKey('body', $requestOptions);
        
        $body = $requestOptions['body'];
        
        $this->assertStringContainsString('<TipoCoche>suv</TipoCoche>', $body);
        $this->assertStringContainsString('<UsoCoche>comercial</UsoCoche>', $body);
        $this->assertStringContainsString('<ConductorOcasional>NO</ConductorOcasional>', $body);
    }

    public function testParseResponse(): void
    {
        $xmlResponse = <<<XML
        <RespuestaCotizacion>
            <Precio>450.75</Precio>
            <Moneda>EUR</Moneda>
        </RespuestaCotizacion>
        XML;
        
        $mockResponse = new MockResponse($xmlResponse);
        $client = new MockHttpClient($mockResponse);
        
        $repository = $this->createStub(ProviderRepository::class);
        $providerEntity = (new Provider())->setUrl('http://test.local')->setHasDiscount(false);
        $repository->method('findOneByName')->willReturn($providerEntity);

        $provider = new ProviderB($client, $repository, $this->serializer);
        
        $response = $client->request('POST', 'http://test.local');
        
        $parsed = $provider->parseResponse($response);

        $this->assertEquals('provider_b', $parsed['provider']);
        $this->assertEquals(450.75, $parsed['price']);
        $this->assertEquals('EUR', $parsed['currency']);
    }
}
