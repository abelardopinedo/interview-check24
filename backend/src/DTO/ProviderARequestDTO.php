<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

use OpenApi\Attributes as OA;

class ProviderARequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 18)]
    #[OA\Property(description: "The age of the driver.", example: 30)]
    public int $driver_age;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['compact', 'suv'])]
    #[OA\Property(description: "The body style of the car.", example: "suv")]
    public string $car_form;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['private', 'commercial'])]
    #[OA\Property(description: "The purpose of car usage.", example: "private")]
    public string $car_use;

    public static function fromCalculateRequestDTO(CalculateRequestDTO $dto): self
    {
        $instance = new self();
        $instance->driver_age = $dto->getAge();
        $instance->car_form = match ($dto->carType) {
            'Turismo', 'Compacto' => 'compact',
            'SUV' => 'suv',
            default => 'compact',
        };
        $instance->car_use = match ($dto->carUse) {
            'Privado' => 'private',
            'Comercial' => 'commercial',
            default => 'private',
        };
        return $instance;
    }
}
