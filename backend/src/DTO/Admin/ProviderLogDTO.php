<?php

namespace App\DTO\Admin;

class ProviderLogDTO
{
    public function __construct(
        public string $providerName,
        public string $status,
        public ?int $httpCode,
        public int $latency,
        public ?string $requestPayload,
        public ?string $responsePayload,
        public string $url,
        public ?string $errorMessage
    ) {}
}
