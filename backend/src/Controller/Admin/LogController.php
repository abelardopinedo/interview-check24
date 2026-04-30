<?php

namespace App\Controller\Admin;

use App\DTO\Admin\LogDetailDTO;
use App\DTO\Admin\LogSummaryDTO;
use App\DTO\Admin\ProviderLogDTO;
use App\Entity\RequestLog;
use App\Repository\ProviderRequestLogRepository;
use App\Repository\RequestLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/admin/logs')]
#[OA\Tag(name: 'Admin Logs')]
class LogController extends AbstractController
{
    public function __construct(
        private RequestLogRepository $requestLogRepository,
        private ProviderRequestLogRepository $providerLogRepository
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'List search logs',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'query', in: 'query', schema: new OA\Schema(type: 'string', nullable: true)),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'integer', nullable: true)),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', enum: ['recent', 'latency']))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $query = $request->query->get('query');
        $status = $request->query->get('status') ? (int) $request->query->get('status') : null;
        $sort = $request->query->get('sort', 'recent');
        
        $offset = ($page - 1) * $limit;

        $logs = $this->requestLogRepository->searchLogs($query, $status, $sort, $limit, $offset);
        $total = $this->requestLogRepository->countSearchLogs($query, $status);
        
        $data = array_map(fn(RequestLog $log) => new LogSummaryDTO(
            $log->getId(),
            $log->getEndpoint(),
            $log->getHttpMethod(),
            $log->getStatusCode(),
            $log->getLatency(),
            $log->getCreatedAt()
        ), $logs);

        return $this->json([
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Get detailed log info with provider waterfall')]
    public function show(int $id): JsonResponse
    {
        $log = $this->requestLogRepository->find($id);

        if (!$log) {
            return $this->json(['error' => 'Log not found'], 404);
        }

        $providerLogs = $this->providerLogRepository->findBy(['request' => $log]);

        $providerLogDTOs = array_map(fn($pLog) => new ProviderLogDTO(
            $pLog->getProvider()->getName(),
            $pLog->getStatus(),
            $pLog->getHttpCode(),
            $pLog->getLatency(),
            $pLog->getRequestPayload(),
            $pLog->getResponsePayload(),
            $pLog->getUrl(),
            $pLog->getErrorMessage()
        ), $providerLogs);

        $detail = new LogDetailDTO(
            $log->getId(),
            $log->getEndpoint(),
            $log->getHttpMethod(),
            $log->getStatusCode(),
            $log->getLatency(),
            $log->getRequestPayload() ?? '',
            $log->getResponsePayload() ?? '',
            $log->getCreatedAt(),
            $providerLogDTOs
        );

        return $this->json($detail);
    }

    #[Route('/stats/performance', methods: ['GET'])]
    #[OA\Get(summary: 'Get performance metrics per provider')]
    public function stats(): JsonResponse
    {
        $stats = $this->providerLogRepository->getPerformanceStats();

        return $this->json($stats);
    }
}
