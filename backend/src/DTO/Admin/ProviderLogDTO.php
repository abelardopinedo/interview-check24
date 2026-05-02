<?php

namespace App\DTO\Admin;

use OpenApi\Attributes as OA;

class ProviderLogDTO
{
    public function __construct(
        #[OA\Property(description: "The name of the insurance provider.", example: "Provider A")]
        public string $providerName,
        #[OA\Property(description: "The outcome of the request (e.g., success, error, timeout).", example: "success")]
        public string $status,
        #[OA\Property(description: "The HTTP status code from the provider's API.", example: 200)]
        public ?int $httpCode,
        #[OA\Property(description: "The latency of the provider's request in milliseconds.", example: 1200)]
        public ?int $latency,
        #[OA\Property(description: "The request payload sent to the provider.", example: '{"driver_age": 30, ...}')]
        public ?string $requestPayload,
        #[OA\Property(description: "The response payload received from the provider.", example: '{"price": "250.0 EUR"}')]
        public ?string $responsePayload,
        #[OA\Property(description: "The URL of the provider's endpoint.", example: "http://provider-a:8001/quote")]
        public string $url,
        #[OA\Property(description: "The error message if the request failed.", example: "Connection timeout", nullable: true)]
        public ?string $errorMessage
    ) {
    }
}
