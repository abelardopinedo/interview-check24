<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProviderBRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 18)]
    public int $EdadConductor;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['turismo', 'compacto', 'suv'])]
    public string $TipoCoche;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['privado', 'comercial'])]
    public string $UsoCoche;

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
