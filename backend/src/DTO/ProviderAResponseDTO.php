<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

class ProviderAResponseDTO
{
    #[OA\Property(description: "The quoted price from Provider A.", example: "250.0 EUR")]
    public string $price;
}
