<?php

namespace App\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\DTO\ProviderAResponseDTO;
use App\DTO\ProviderARequestDTO;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP Client Adapter for Provider A.
 * Communicates with the JSON-based pricing endpoint and maps responses to DTOs.
 */
class ProviderA extends AbstractProvider
{
    public function getInternalKey(): string
    {
        return 'provider_a';
    }

    public function getQuote(CalculateRequestDTO $request): array
    {
        $providerRequest = ProviderARequestDTO::fromCalculateRequestDTO($request);
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

        $dto = $this->serializer->deserialize($jsonContent, ProviderAResponseDTO::class, 'json');

        preg_match('/[\d.]+/', $dto->price, $matches);

        return [
            'provider' => $this->getName(),
            'price' => (float) ($matches[0] ?? 0),
            'currency' => 'EUR'
        ];
    }

}
