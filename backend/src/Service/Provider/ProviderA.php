<?php

namespace App\Service\Provider;

use App\DTO\CalculateRequestDTO;
use App\DTO\ProviderAResponseDTO;
use App\DTO\ProviderARequestDTO;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\ProviderRepository;

/**
 * HTTP Client Adapter for Provider A.
 * Communicates with the JSON-based pricing endpoint and maps responses to DTOs.
 */
class ProviderA implements ProviderInterface
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
        $providerRequest = ProviderARequestDTO::fromCalculateRequestDTO($request);
        $jsonBody = $this->serializer->serialize($providerRequest, 'json');

        return $this->client->request('POST', $this->url, [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $jsonBody,
        ]);
    }

    public function getUrl()
    {
        return $this->url;
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

    public function getName(): string
    {
        return 'provider_a';
    }


    public function hasCampaignDiscount(): bool
    {
        return $this->hasDiscount;
    }

}
