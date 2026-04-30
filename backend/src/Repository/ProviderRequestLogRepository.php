<?php

namespace App\Repository;

use App\Entity\ProviderRequestLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProviderRequestLog>
 *
 * @method ProviderRequestLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderRequestLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderRequestLog[]    findAll()
 * @method ProviderRequestLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderRequestLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProviderRequestLog::class);
    }
}
