<?php

namespace App\Service\Provider;


use App\DTO\CalculateRequestDTO;
use Symfony\Contracts\HttpClient\ResponseInterface;

use App\Entity\Provider;

interface ProviderInterface
{


    /**
     * @return array{response: ResponseInterface, payload: string}
     */
    public function getQuote(CalculateRequestDTO $request): array;

    public function parseResponse(ResponseInterface $response): array;

    public function getName(): string;

    public function hasCampaignDiscount(): bool;

    public function getProviderEntity(): Provider;

    public function getUrl(): string;
}
