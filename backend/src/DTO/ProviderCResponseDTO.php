<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

class ProviderCResponseDTO
{
    /** @var array{price: string} */
    #[OA\Property(
        description: "The response payload containing the price.",
        properties: [
            new OA\Property(property: "price", type: "string", example: "450.00 EUR")
        ],
        type: "object"
    )]
    public array $payload;
}
