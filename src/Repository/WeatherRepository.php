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
    private const CACHE_LIFETIME = 120;

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
     * @return Weather[]
     */
    public function findLatest(int $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('w');
        $queryBuilder->orderBy($queryBuilder->expr()->desc('w.took'))
            ->setMaxResults($limit)
            ->setLifetime(self::CACHE_LIFETIME);
        return $queryBuilder->getQuery()->getResult();
    }
}
