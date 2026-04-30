<?php

namespace App\DTO\Admin;

class LogDetailDTO
{
    /**
     * @param ProviderLogDTO[] $providerLogs
     */
    public function __construct(
        public int $id,
        public string $endpoint,
        public string $httpMethod,
        public int $statusCode,
        public int $latency,
        public string $requestPayload,
        public string $responsePayload,
        public \DateTimeImmutable $createdAt,
        public array $providerLogs
    ) {}
}
