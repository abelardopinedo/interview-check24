<?php

namespace App\Service\Provider;


use App\DTO\CalculateRequestDTO;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ProviderInterface
{


    public function getQuote(CalculateRequestDTO $request): mixed;

    public function parseResponse(ResponseInterface $response): array;

    public function getName(): string;

    public function hasCampaignDiscount(): bool;
}
