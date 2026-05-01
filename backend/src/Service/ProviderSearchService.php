<?php

namespace App\Service;

use App\DTO\CalculateRequestDTO;
use App\Entity\ProviderRequestLog;
use App\Entity\RequestLog;
use App\Service\Provider\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProviderSearchService
{
    /**
     * @param iterable<ProviderInterface> $providers
     */
    public function __construct(
        private iterable $providers,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private CurrentRequestLogStore $logStore
    ) {
    }

    /**
     * Fetches quotes from all providers in parallel, normalizes the responses, 
     * applies campaign discounts, and sorts the final list by price ascending.
     * Any provider that throws an error or times out is gracefully ignored.
     */
    public function findAll(CalculateRequestDTO $requestDto): array
    {
        $requestLog = $this->logStore->getRequestLog();

        $providerMap = [];
        $responses = [];
        $results = [];
        $providerLogs = [];

        // 2. Initiate all requests asynchronously (Parallel fetching)
        foreach ($this->providers as $provider) {
            $providerStartTime = microtime(true);
            $quoteData = $provider->getQuote($requestDto);
            $response = $quoteData['response'];
            $providerPayload = $quoteData['payload'];

            $responses[] = $response;
            $providerMap[spl_object_id($response)] = $provider;

            // Create a log entry for this provider call
            $pLog = new ProviderRequestLog();
            $pLog->setRequest($requestLog);
            $pLog->setProvider($provider->getProviderEntity());
            $pLog->setUrl($provider->getUrl());
            $pLog->setRequestPayload($providerPayload);
            $pLog->setStatus('pending');
            $this->entityManager->persist($pLog);

            $providerLogs[spl_object_id($response)] = [
                'log' => $pLog,
                'start_time' => $providerStartTime
            ];
        }

        // 3. Resolve requests and process the successful ones
        foreach ($responses as $response) {
            /** @var ProviderInterface $provider */
            $provider = $providerMap[spl_object_id($response)];
            $pData = $providerLogs[spl_object_id($response)];
            /** @var ProviderRequestLog $pLog */
            $pLog = $pData['log'];

            try {
                $statusCode = $response->getStatusCode();
                $pLog->setHttpCode($statusCode);
                $pLog->setLatency((int) ((microtime(true) - $pData['start_time']) * 1000));
                $pLog->setResponsePayload($response->getContent(false));

                if ($statusCode === 200) {
                    $pLog->setStatus('completed');
                    $normalized = $provider->parseResponse($response);
                    $results[] = $provider->applyDiscounts($normalized);
                } else {
                    $pLog->setStatus('failed');
                }
            } catch (\Exception $e) {
                $pLog->setStatus('failed');
                $pLog->setErrorMessage($e->getMessage());
                continue;
            }
        }

        // 4. Sort final results from cheapest to most expensive (considering discounts)
        usort($results, fn($a, $b) => ($a['discount_price'] ?? $a['price']) <=> ($b['discount_price'] ?? $b['price']));

        // 5. Provider logs are already linked and persisted. 
        // Flush is handled by the RequestLoggerSubscriber at the end of the request.

        return $results;
    }
}
