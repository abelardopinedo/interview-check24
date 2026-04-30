<?php

namespace App\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\DTO\ProviderBResponseDTO;
use App\DTO\ProviderBRequestDTO;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use App\Repository\ProviderRepository;

/**
 * HTTP Client Adapter for Provider B.
 * Communicates with the XML-based pricing endpoint and maps responses to DTOs.
 */
class ProviderB implements ProviderInterface
{


    private ?string $url = null;
    private ?bool $hasDiscount = null;

    public function __construct(
        private HttpClientInterface $client,
        private ProviderRepository $repository,
        private SerializerInterface $serializer
    ) {
        $provider = $this->repository->findOneByName($this->getName());
        if ($provider) {
            $this->url = $provider->getUrl();
            $this->hasDiscount = $provider->isHasDiscount();
        }
    }

    public function getQuote(CalculateRequestDTO $request): mixed
    {
        $providerRequest = ProviderBRequestDTO::fromCalculateRequestDTO($request);

        $xmlBody = $this->serializer->serialize($providerRequest, 'xml', [
            'xml_root_node_name' => 'SolicitudCotizacion'
        ]);

        return $this->client->request('POST', $this->url, [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/xml'],
            'body' => $xmlBody,
        ]);
    }

    public function getName(): string
    {
        return 'provider_b';
    }


    public function hasCampaignDiscount(): bool
    {
        return $this->hasDiscount;
    }

    public function getUrl()
    {
        return $this->url;
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
