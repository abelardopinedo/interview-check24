<?php

namespace App\Repository;

use App\Entity\RequestLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestLog>
 *
 * @method RequestLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestLog[]    findAll()
 * @method RequestLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestLog::class);
    }

    /**
     * @return RequestLog[]
     */
    public function searchLogs(
        ?string $query = null,
        ?int $status = null,
        string $sort = 'recent',
        int $limit = 20,
        int $offset = 0
    ): array {
        $qb = $this->createQueryBuilder('rl');

        if ($query) {
            if (is_numeric($query)) {
                $qb->andWhere('rl.id = :id')
                   ->setParameter('id', (int)$query);
            } else {
                // Search by provider name in related ProviderRequestLog
                $qb->leftJoin('App\Entity\ProviderRequestLog', 'prl', 'WITH', 'prl.request = rl')
                   ->leftJoin('prl.provider', 'p')
                   ->andWhere('p.name LIKE :search')
                   ->setParameter('search', '%' . $query . '%')
                   ->distinct();
            }
        }

        if ($status) {
            $qb->andWhere('rl.statusCode = :status')
               ->setParameter('status', $status);
        }

        if ($sort === 'latency') {
            $qb->orderBy('rl.latency', 'DESC');
        } else {
            $qb->orderBy('rl.createdAt', 'DESC');
        }

        $qb->setMaxResults($limit)
           ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countSearchLogs(?string $query = null, ?int $status = null): int
    {
        $qb = $this->createQueryBuilder('rl')
                   ->select('count(DISTINCT rl.id)');

        if ($query) {
            if (is_numeric($query)) {
                $qb->andWhere('rl.id = :id')
                   ->setParameter('id', (int)$query);
            } else {
                $qb->leftJoin('App\Entity\ProviderRequestLog', 'prl', 'WITH', 'prl.request = rl')
                   ->leftJoin('prl.provider', 'p')
                   ->andWhere('p.name LIKE :search')
                   ->setParameter('search', '%' . $query . '%');
            }
        }

        if ($status) {
            $qb->andWhere('rl.statusCode = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
