<?php

namespace App\Tests\Controller;

use App\Controller\ProviderMockController;
use PHPUnit\Framework\TestCase;

class ProviderMockControllerTest extends TestCase
{
    private ProviderMockController $controller;

    protected function setUp(): void
    {
        $this->controller = new ProviderMockController();
    }

    private function invokePrivateMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        return $method->invokeArgs($object, $parameters);
    }

    public function testCalculateProviderAPriceValidRequest(): void
    {
        $input = [
            'driver_age' => 30,
            'car_form' => 'suv',
            'car_use' => 'private'
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderAPrice', [$input]);

        // Base: 217
        // Age (30 -> >24): 0
        // Form (suv): 100
        // Use (private): * 1
        // Total: 317
        $this->assertEquals(317.0, $price);
    }

    public function testCalculateProviderAPriceYoungDriverCommercialUse(): void
    {
        $input = [
            'driver_age' => 20,
            'car_form' => 'compact',
            'car_use' => 'commercial'
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderAPrice', [$input]);

        // Base: 217
        // Age (20 -> >=18): +70
        // Form (compact): +10
        // Subtotal: 297
        // Use (commercial): 297 * 1.15 = 341.55
        $this->assertEqualsWithDelta(341.55, $price, 0.01);
    }

    public function testCalculateProviderAPriceMiddleDriverPrivateUse(): void
    {
        $input = [
            'driver_age' => 25,
            'car_form' => 'compact',
            'car_use' => 'private'
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderAPrice', [$input]);

        // Base: 217
        // Age (25 -> >24): +0
        // Form (compact): +10
        // Subtotal: 227
        // Use (private): 227 * 1 = 227
        $this->assertEqualsWithDelta(227.0, $price, 0.01);
    }

    public function testCalculateProviderAPriceOldDriverPrivateUse(): void
    {
        $input = [
            'driver_age' => 65,
            'car_form' => 'compact',
            'car_use' => 'private'
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderAPrice', [$input]);

        // Base: 217
        // Age (65 -> >56): +90
        // Form (compact): +10
        // Subtotal: 317
        // Use (private): 317 * 1 = 317
        $this->assertEqualsWithDelta(317.0, $price, 0.01);
    }

    public function testCalculateProviderAPriceInvalidAge(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Age');

        $input = [
            'driver_age' => 17,
            'car_form' => 'compact',
            'car_use' => 'private'
        ];

        $this->invokePrivateMethod($this->controller, 'calculateProviderAPrice', [$input]);
    }

    public function testCalculateProviderBPriceValidRequest(): void
    {
        $input = [
            "EdadConductor" => 30,
            "TipoCoche" => "turismo",
            "UsoCoche" => "privado",
            "ConductorOcasional" => "NO"
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderBPrice', [$input]);

        // Base: 250
        // Age (30 -> >29): 20
        // Form (turismo): 30
        // Total: 300.0

        $this->assertEquals(300.0, $price);
    }
    public function testCalculateProviderBPriceYoungDriverCompact(): void
    {
        $input = [
            "EdadConductor" => 19,
            "TipoCoche" => "compacto",
            "UsoCoche" => "privado",
            "ConductorOcasional" => "NO"
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderBPrice', [$input]);

        // Base: 250
        // Age (19 -> >=18): 50
        // Form (compacto): 0
        // Total: 300

        $this->assertEquals(300.0, $price);
    }

    public function testCalculateProviderBPriceMiddleDriverTurismo(): void
    {
        $input = [
            "EdadConductor" => 30,
            "TipoCoche" => "turismo",
            "UsoCoche" => "privado",
            "ConductorOcasional" => "NO"
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderBPrice', [$input]);

        // Base: 250
        // Age (30 -> >29): 20
        // Form (turismo): 30
        // Total: 300.0

        $this->assertEquals(300.0, $price);
    }

    public function testCalculateProviderBPriceOldDriverSuv(): void
    {
        $input = [
            "EdadConductor" => 65,
            "TipoCoche" => "suv",
            "UsoCoche" => "privado",
            "ConductorOcasional" => "NO"
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderBPrice', [$input]);

        // Base: 250
        // Age (65 -> >59): 100
        // Form (suv): 200
        // Total: 550.0

        $this->assertEquals(550.0, $price);
    }

    public function testCalculateProviderBPriceInvalidCarForm(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid TipoCoche');

        $input = [
            "EdadConductor" => 30,
            "TipoCoche" => "spaceship",
            "UsoCoche" => "privado",
            "ConductorOcasional" => "NO"
        ];

        $this->invokePrivateMethod($this->controller, 'calculateProviderBPrice', [$input]);
    }

    public function testCalculateProviderCPriceValidRequest(): void
    {
        $input = [
            'driverInfo' => ['age' => 45],
            'carInfo' => [
                'car_form' => 'suv',
                'car_use' => 'private'
            ]
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderCPrice', [$input]);

        // Base: 185
        // Age (45 -> >24): +20
        // Form (suv): +120
        // Subtotal: 325
        // Use (private): * 1
        // Total: 325
        $this->assertEquals(325.0, $price);
    }

    public function testCalculateProviderCPriceYoungDriverCommercialUse(): void
    {
        $input = [
            'driverInfo' => ['age' => 20],
            'carInfo' => [
                'car_form' => 'compact',
                'car_use' => 'commercial'
            ]
        ];

        $price = $this->invokePrivateMethod($this->controller, 'calculateProviderCPrice', [$input]);

        // Base: 185
        // Age (20 -> >=18): +90
        // Form (compact): +30
        // Subtotal: 305
        // Use (commercial): 305 * 1.2 = 366
        $this->assertEqualsWithDelta(366.0, $price, 0.01);
    }
}