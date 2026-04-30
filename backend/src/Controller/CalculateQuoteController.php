<?php

namespace App\Controller;

use App\DTO\CalculateRequestDTO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

use App\Service\ProviderSearchService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;


final class CalculateQuoteController extends AbstractController
{
    public function __construct(
        private ProviderSearchService $searchService
    ) {}

    #[Route('/calculate', methods: ['POST'])]
    #[OA\Post(
        summary: 'Calculate insurance quotes',
        description: 'Fetches and aggregates insurance quotes from multiple providers based on driver and car details.'
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an array of quotes sorted by price.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'provider', type: 'string', example: 'provider_a'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 295.0),
                    new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
                    new OA\Property(property: 'discount_price', type: 'number', format: 'float', example: 280.25, nullable: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error in the request payload.'
    )]
    public function calculate(
        #[MapRequestPayload] CalculateRequestDTO $requestDto
    ): JsonResponse {

        $quotes = $this->searchService->findAll($requestDto);

        return $this->json($quotes);
    }
}
