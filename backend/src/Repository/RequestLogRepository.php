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
        int $offset = 0,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('rl');

        if ($query) {
            if (is_numeric($query)) {
                $qb->andWhere('rl.id = :id')
                   ->setParameter('id', (int)$query);
            }
        }

        if ($startDate) {
            $qb->andWhere('rl.createdAt >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('rl.createdAt <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
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

    public function countSearchLogs(
        ?string $query = null,
        ?int $status = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int {
        $qb = $this->createQueryBuilder('rl')
                   ->select('count(DISTINCT rl.id)');

        if ($query) {
            if (is_numeric($query)) {
                $qb->andWhere('rl.id = :id')
                   ->setParameter('id', (int)$query);
            }
        }

        if ($startDate) {
            $qb->andWhere('rl.createdAt >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('rl.createdAt <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        if ($status) {
            $qb->andWhere('rl.statusCode = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
