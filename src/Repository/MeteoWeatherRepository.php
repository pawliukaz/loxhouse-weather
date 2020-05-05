<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\MeteoWeather;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class WeatherRepository
 * @package App\Repository
 * @method MeteoWeather|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeteoWeather|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeteoWeather[]    findAll()
 * @method MeteoWeather[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeteoWeatherRepository extends ServiceEntityRepository
{
    /**
     * WeatherRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeteoWeather::class);
    }

    /**
     * @param string $place
     * @return MeteoWeather|null
     * @throws NonUniqueResultException
     */
    public function findLatest(string $place): ?MeteoWeather
    {
        $queryBuilder = $this->createQueryBuilder('mw');
        $queryBuilder->where($queryBuilder->expr()->eq('mw.place', ':place'))
            ->setParameter('place', $place)
            ->orderBy($queryBuilder->expr()->desc('mw.took'));
        $query = $queryBuilder->getQuery();
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }
}
