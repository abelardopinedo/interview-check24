<?php

namespace App\DTO\Admin;

use OpenApi\Attributes as OA;

class LogSummaryDTO
{
    public function __construct(
        #[OA\Property(description: "The unique identifier of the log entry.", example: 123)]
        public int $id,
        #[OA\Property(description: "The API endpoint that was called.", example: "/calculate")]
        public string $endpoint,
        #[OA\Property(description: "The HTTP method used for the request.", example: "POST")]
        public string $httpMethod,
        #[OA\Property(description: "The HTTP status code returned by the server.", example: 200)]
        public ?int $statusCode,
        #[OA\Property(description: "The request latency in milliseconds.", example: 450)]
        public ?int $latency,
        #[OA\Property(description: "The date and time when the request was made.")]
        public \DateTimeImmutable $createdAt
    ) {}
}
