<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

use OpenApi\Attributes as OA;

class ProviderBResponseDTO
{
    #[SerializedName('Precio')]
    #[OA\Property(description: "The price of the insurance quote (Spanish: Precio).", example: 325.5)]
    public float $Precio;

    #[SerializedName('Moneda')]
    #[OA\Property(description: "The currency of the price (Spanish: Moneda).", example: "EUR")]
    public string $Moneda;
}
