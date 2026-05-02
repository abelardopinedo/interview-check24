<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\DTO\ProviderARequestDTO;
use App\DTO\ProviderAResponseDTO;
use App\DTO\ProviderBRequestDTO;
use App\DTO\ProviderBResponseDTO;
use App\DTO\ProviderCRequestDTO;
use App\DTO\ProviderCResponseDTO;



#[OA\Tag(name: 'Provider Mocks')]
final class ProviderMockController extends AbstractController
{
    private const CURRENCY = 'EUR';

    private function calculateProviderAPrice(array $input): float
    {
        $price = 217;

        $price += match (true) {
            $input['driver_age'] > 56 => 90,
            $input['driver_age'] > 24 => 0,
            $input['driver_age'] >= 18 => 70,
            default => throw new \Exception("Invalid Age"),
        };

        $price += match ($input['car_form']) {
            "suv" => 100,
            "compact" => 10,
            default => throw new \Exception("Invalid Car Form"),
        };

        $price *= match ($input['car_use']) {
            "private" => 1,
            "commercial" => 1.15,
            default => throw new \Exception("Invalid Car Use"),
        };

        return $price;
    }

    #[Route('/provider-a/quote', methods: ['POST'])]
    #[OA\Post(
        summary: 'Mock endpoint for Provider A',
        description: 'Simulates Provider A pricing engine (JSON). Note: Includes artificial delay and 10% chance of 500 error.'
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: ProviderARequestDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful quote',
        content: new OA\JsonContent(ref: new Model(type: ProviderAResponseDTO::class))
    )]
    public function providerAMock(Request $request): JsonResponse
    {
        //Emulating delay + Instability
        sleep(2);
        if (random_int(1, 100) <= 10) {
            return $this->json(['error' => 'Internal Server Error'], 500);
        }

        try {
            $input = json_decode($request->getContent(), true);
            $price = $this->calculateProviderAPrice($input);

            return $this->json([
                'price' => $price . " " . self::CURRENCY
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    private function calculateProviderBPrice(array $input): float
    {
        $price = 250;

        // We check for age and add accordingly, if the age is lower than 18 we throw an error
        $price += match (true) {
            $input["EdadConductor"] > 59 => 100,
            $input["EdadConductor"] > 29 => 20,
            $input["EdadConductor"] >= 18 => 50,
            default => throw new \Exception("Invalid EdadConductor"),
        };

        // We check for car form and add accordingly, if car form is not valid, we throw an error
        $price += match ($input["TipoCoche"]) {
            "suv" => 200,
            "compacto" => 0,
            "turismo" => 30,
            default => throw new \Exception("Invalid TipoCoche"),
        };

        return (float) number_format((float) $price, 1, '.', '');
    }

    #[Route('/provider-b/quote', methods: ['POST'])]
    #[OA\Post(
        summary: 'Mock endpoint for Provider B',
        description: 'Simulates Provider B pricing engine (XML). Note: Includes artificial delay of 5 seconds and 1% chance of 60 seconds.'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\XmlContent(ref: new Model(type: ProviderBRequestDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful XML quote',
        content: new OA\XmlContent(ref: new Model(type: ProviderBResponseDTO::class))
    )]
    public function providerBMock(Request $request, DecoderInterface $decoder, SerializerInterface $serializer): Response
    {
        //Emulating random delay
        $delay = random_int(1, 100) > 1 ? 5 : 60;
        sleep($delay);

        try {
            $input = $decoder->decode($request->getContent(), 'xml');
            $price = $this->calculateProviderBPrice($input);

            $output = [
                "Precio" => $price,
                "Moneda" => self::CURRENCY
            ];

            $xmlContent = $serializer->serialize($output, 'xml', [
                'xml_root_node_name' => 'RespuestaCotizacion'
            ]);

            return new Response($xmlContent, 200, [
                'Content-Type' => 'application/xml'
            ]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 422);
        }
    }

    private function calculateProviderCPrice(array $input): float
    {
        $price = 185;

        $driverAge = $input['driverInfo']['age'];
        $carForm = $input['carInfo']['car_form'];
        $carUse = $input['carInfo']['car_use'];

        $price += match (true) {
            $driverAge > 56 => 120,
            $driverAge > 24 => 20,
            $driverAge >= 18 => 90,
            default => throw new \Exception("Invalid Age"),
        };

        $price += match ($carForm) {
            "suv" => 120,
            "compact" => 30,
            default => throw new \Exception("Invalid Car Form"),
        };

        $price *= match ($carUse) {
            "private" => 1,
            "commercial" => 1.2,
            default => throw new \Exception("Invalid Car Use"),
        };

        return $price;
    }

    #[Route('/provider-c/quote', methods: ['POST'])]
    #[OA\Post(
        summary: 'Mock endpoint for Provider C',
        description: 'Simulates Provider C pricing engine (Nested JSON).'
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: ProviderCRequestDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful quote',
        content: new OA\JsonContent(ref: new Model(type: ProviderCResponseDTO::class))
    )]
    public function providerCMock(Request $request): JsonResponse
    {

        sleep(3);

        try {
            $input = json_decode($request->getContent(), true);
            $price = $this->calculateProviderCPrice($input);

            return $this->json([
                'payload' => ['price' => $price . " " . self::CURRENCY]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

}
