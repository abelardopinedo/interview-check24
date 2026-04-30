<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProviderARequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 18)]
    public int $driver_age;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['compact', 'suv'])]
    public string $car_form;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['private', 'commercial'])]
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
