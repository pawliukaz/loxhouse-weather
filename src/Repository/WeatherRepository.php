<?php

namespace App\Repository;

use App\Entity\Weather;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class WeatherRepository
 * @package App\Repository
 * @method Weather|null find($id, $lockMode = null, $lockVersion = null)
 * @method Weather|null findOneBy(array $criteria, array $orderBy = null)
 * @method Weather[]    findAll()
 * @method Weather[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeatherRepository extends ServiceEntityRepository
{
    private const CACHE_LIFETIME = 30;

    /**
     * WeatherRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Weather::class);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Weather[]
     */
    public function findLatest(int $limit = 10, int $offset = 0)
    {
        $queryBuilder = $this->createQueryBuilder('w');
        $queryBuilder->orderBy($queryBuilder->expr()->desc('w.took'))
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        return $queryBuilder->getQuery()->setCacheable(true)->getResult();
    }

    /**
     * @return int
     */
    public function countAll(): int
    {
        // NOTE: COUNT(...) returns a scalar result. Doctrine's second level
        // cache (setCacheable()) only supports entity results and throws
        // "Second level cache does not support scalar results." for scalar/
        // aggregate queries. Use the regular query result cache instead,
        // which supports scalar results just fine.
        return (int)$this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->getQuery()
            ->useResultCache(true, self::CACHE_LIFETIME)
            ->getSingleScalarResult();
    }
}
