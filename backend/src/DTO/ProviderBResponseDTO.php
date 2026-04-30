<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ProviderBResponseDTO
{
    #[SerializedName('Precio')]
    public float $Precio;

    #[SerializedName('Moneda')]
    public string $Moneda;
}
