<?php

namespace App\DTO\Admin;

class LogSummaryDTO
{
    public function __construct(
        public int $id,
        public string $endpoint,
        public string $httpMethod,
        public int $statusCode,
        public int $latency,
        public \DateTimeImmutable $createdAt
    ) {}
}
