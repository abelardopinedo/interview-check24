<?php

namespace App\DTO;

class ProviderCRequestDTO
{
    public array $driverInfo;
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
