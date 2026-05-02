<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use OpenApi\Attributes as OA;

class CalculateRequestDTO
{
    #[Assert\NotBlank(message: 'El parametro fecha de nacimiento no es opcional.')]
    #[Assert\Date]
    #[SerializedName("driver_birthday")]
    #[OA\Property(description: "The driver's date of birth in YYYY-MM-DD format.", example: "1990-05-15")]
    public string $driverBirthday;


    #[Assert\NotBlank(message: 'El parametro Tipo de Coche no es opcional.')]
    #[Assert\Choice(choices: ['Turismo', 'SUV', 'Compacto'])]
    #[OA\Property(description: "The type of the car being insured.", example: "Turismo")]
    #[SerializedName("car_type")]
    public string $carType;


    #[Assert\NotBlank(message: "El parametro Uso del coche no es opcional.")]
    #[Assert\Choice(choices: ['Privado', 'Comercial'])]
    #[OA\Property(description: "The primary usage of the car.", example: "Privado")]
    #[SerializedName("car_use")]
    public string $carUse;

    private int $age;

    public function __construct(string $driverBirthday, string $carType, string $carUse)
    {
        $this->driverBirthday = $driverBirthday;
        $this->carType = $carType;
        $this->carUse = $carUse;

        // Transforms driverBirthday to Age
        $birthDate = new \DateTime($driverBirthday);
        $today = new \DateTime();
        $this->age = $today->diff($birthDate)->y;

        // If the birthday is in the future, set age to -1
        if ($today < $birthDate) {
            $this->age = -1;
        }
    }

    #[Assert\Callback]
    public function validateAge(ExecutionContextInterface $context, $payload): void
    {

        if ($this->age < 0) {
            $context->buildViolation('No puede estar en el futuro.')
                ->atPath('Fecha de nacimiento')
                ->addViolation();
        } else if ($this->age < 18) {
            $context->buildViolation('Debes tener al menos 18 años para solicitar un seguro.')
                ->atPath('Fecha de nacimiento')
                ->addViolation();
        } else if ($this->age > 123) {
            $context->buildViolation('Por favor, introduce una edad válida (menor de 123 años).')
                ->atPath('Fecha de nacimiento')
                ->addViolation();
        }
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
