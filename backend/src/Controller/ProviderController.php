<?php

namespace App\Controller;

use App\DTO\UpdateProviderDTO;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[Route('/providers')]
class ProviderController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all providers',
        description: 'Returns a list of all insurance providers stored in the database.'
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Provider::class))
        )
    )]
    public function index(): JsonResponse
    {
        $providers = $this->providerRepository->findAll();
        return $this->json($providers);
    }

    #[Route('/search', methods: ['GET'])]
    #[OA\Get(
        summary: 'Search for a provider name',
        description: 'Searches for providers by name using a partial match.'
    )]
    #[OA\Parameter(
        name: 'q',
        in: 'query',
        description: 'The search query string',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Provider::class))
        )
    )]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (empty($query)) {
            return $this->json(['error' => 'Query parameter "q" is required'], Response::HTTP_BAD_REQUEST);
        }

        $providers = $this->providerRepository->searchByName($query);
        return $this->json($providers);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get provider information by id',
        description: 'Returns details of a specific insurance provider.'
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(ref: new Model(type: Provider::class))
    )]
    #[OA\Response(response: 404, description: 'Provider not found')]
    public function show(int $id): JsonResponse
    {
        $provider = $this->providerRepository->find($id);
        if (!$provider) {
            return $this->json(['error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($provider);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Update provider',
        description: 'Updates provider details (name, url, or has_discount).'
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: UpdateProviderDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Provider updated successfully',
        content: new OA\JsonContent(ref: new Model(type: Provider::class))
    )]
    #[OA\Response(response: 404, description: 'Provider not found')]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateProviderDTO $dto
    ): JsonResponse {
        $provider = $this->providerRepository->find($id);
        if (!$provider) {
            return $this->json(['error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        if ($dto->url !== null) {
            $provider->setUrl($dto->url);
        }
        if ($dto->hasDiscount !== null) {
            $provider->setHasDiscount($dto->hasDiscount);
        }

        $this->entityManager->flush();

        return $this->json($provider);
    }
}
