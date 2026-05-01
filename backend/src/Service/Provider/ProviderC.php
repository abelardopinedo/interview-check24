<?php

namespace App\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\DTO\ProviderCResponseDTO;
use App\DTO\ProviderCRequestDTO;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP Client Adapter for Provider C.
 * Communicates with the nested JSON-based pricing endpoint and maps responses to DTOs.
 */
class ProviderC extends AbstractProvider
{
    public function getInternalKey(): string
    {
        return 'provider_c';
    }

    public function getQuote(CalculateRequestDTO $request): array
    {
        $providerRequest = ProviderCRequestDTO::fromCalculateRequestDTO($request);
        $jsonBody = $this->serializer->serialize($providerRequest, 'json');

        $response = $this->client->request('POST', $this->url, [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $jsonBody,
        ]);

        return [
            'response' => $response,
            'payload' => $jsonBody
        ];
    }

    public function parseResponse(ResponseInterface $response): array
    {
        $jsonContent = $response->getContent();

        /** @var ProviderCResponseDTO $dto */
        $dto = $this->serializer->deserialize($jsonContent, ProviderCResponseDTO::class, 'json');

        $priceString = $dto->payload['price'] ?? '0';
        preg_match('/[\d.]+/', $priceString, $matches);

        return [
            'provider' => $this->getName(),
            'price' => (float) ($matches[0] ?? 0),
            'currency' => 'EUR'
        ];
    }
}
