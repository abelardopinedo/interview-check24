<?php

namespace App\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\DTO\ProviderBResponseDTO;
use App\DTO\ProviderBRequestDTO;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP Client Adapter for Provider B.
 * Communicates with the XML-based pricing endpoint and maps responses to DTOs.
 */
class ProviderB extends AbstractProvider
{
    public function getInternalKey(): string
    {
        return 'provider_b';
    }

    public function getQuote(CalculateRequestDTO $request): array
    {
        $providerRequest = ProviderBRequestDTO::fromCalculateRequestDTO($request);

        $xmlBody = $this->serializer->serialize($providerRequest, 'xml', [
            'xml_root_node_name' => 'SolicitudCotizacion'
        ]);

        $response = $this->client->request('POST', $this->url, [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/xml'],
            'body' => $xmlBody,
        ]);

        return [
            'response' => $response,
            'payload' => $xmlBody
        ];
    }


    public function parseResponse(ResponseInterface $response): array
    {
        $xmlContent = $response->getContent();

        $dto = $this->serializer->deserialize($xmlContent, ProviderBResponseDTO::class, 'xml');

        return [
            'provider' => $this->getName(),
            'price' => $dto->Precio,
            'currency' => $dto->Moneda ?? 'EUR'
        ];
    }

}
