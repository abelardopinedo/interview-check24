<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class UpdateProviderDTO
{

    #[Assert\Url(requireTld: false)]
    #[Assert\Length(max: 255)]
    #[OA\Property(description: "The API URL of the provider.", example: "https://api.insurance.com")]
    public ?string $url = null;

    #[SerializedName("has_discount")]
    #[OA\Property(description: "Whether the provider has an active discount.", example: true)]
    public ?bool $hasDiscount = null;
}
