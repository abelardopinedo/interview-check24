<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

class ProviderCRequestDTO
{
    #[OA\Property(
        description: "Information about the driver.",
        properties: [
            new OA\Property(property: "age", type: "integer", example: 45)
        ],
        type: "object"
    )]
    public array $driverInfo;

    #[OA\Property(
        description: "Information about the car.",
        properties: [
            new OA\Property(property: "car_form", type: "string", example: "suv"),
            new OA\Property(property: "car_use", type: "string", example: "private")
        ],
        type: "object"
    )]
    public array $carInfo;

    public static function fromCalculateRequestDTO(CalculateRequestDTO $dto): self
    {
        $instance = new self();
        $instance->driverInfo = [
            'age' => $dto->getAge()
        ];
        $instance->carInfo = [
            'car_form' => match ($dto->carType) {
                'Turismo', 'Compacto' => 'compact',
                'SUV' => 'suv',
                default => 'compact',
            },
            'car_use' => match ($dto->carUse) {
                'Privado' => 'private',
                'Comercial' => 'commercial',
                default => 'private',
            },
        ];
        return $instance;
    }
}
