<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

use OpenApi\Attributes as OA;

class ProviderBRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 18)]
    #[OA\Property(description: "The age of the driver (Spanish: Edad del conductor).", example: 30)]
    public int $EdadConductor;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['turismo', 'compacto', 'suv'])]
    #[OA\Property(description: "The type of car (Spanish: Tipo de coche).", example: "turismo")]
    public string $TipoCoche;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['privado', 'comercial'])]
    #[OA\Property(description: "The usage of the car (Spanish: Uso del coche).", example: "privado")]
    public string $UsoCoche;

    #[Assert\Choice(choices: ['SI', 'NO'])]
    #[OA\Property(description: "Whether there is an occasional driver (Spanish: Conductor ocasional).", example: "NO")]
    public string $ConductorOcasional = 'NO';

    public static function fromCalculateRequestDTO(CalculateRequestDTO $dto): self
    {
        $instance = new self();
        $instance->EdadConductor = $dto->getAge();
        $instance->TipoCoche = match ($dto->carType) {
            'Turismo' => 'turismo',
            'Compacto' => 'compacto',
            'SUV' => 'suv',
            default => 'compacto',
        };
        $instance->UsoCoche = match ($dto->carUse) {
            'Privado' => 'privado',
            'Comercial' => 'comercial',
            default => 'privado',
        };
        return $instance;
    }
}
