<?php

namespace App\Service;

use App\DTO\CalculateRequestDTO;
use App\Service\Provider\ProviderInterface;

class ProviderSearchService
{


    /**
     * @param iterable<ProviderInterface> $providers
     */
    public function __construct(
        private iterable $providers
    ) {
    }

    /**
     * Fetches quotes from all providers in parallel, normalizes the responses, 
     * applies campaign discounts, and sorts the final list by price ascending.
     * Any provider that throws an error or times out is gracefully ignored.
     */
    public function findAll(CalculateRequestDTO $requestDto): array
    {
        $providerMap = [];
        $responses = [];
        $results = [];

        // 1. Initiate all requests asynchronously (Parallel fetching)
        foreach ($this->providers as $provider) {
            $response = $provider->getQuote($requestDto);

            $responses[] = $response;
            $providerMap[spl_object_id($response)] = $provider;
        }

        // 2. Resolve requests and process the successful ones
        foreach ($responses as $response) {
            /** @var ProviderInterface $provider */
            $provider = $providerMap[spl_object_id($response)];

            try {
                if ($response->getStatusCode() === 200) {
                    $normalized = $provider->parseResponse($response);

                    // Apply a flat 5% discount if the provider is enrolled in the campaign
                    if ($provider->hasCampaignDiscount()) {
                        $normalized['discount_price'] = round($normalized['price'] * 0.95, 2);
                    }

                    $results[] = $normalized;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // 3. Sort final results from cheapest to most expensive (considering discounts)
        usort($results, fn($a, $b) => ($a['discount_price'] ?? $a['price']) <=> ($b['discount_price'] ?? $b['price']));

        return $results;
    }
}
