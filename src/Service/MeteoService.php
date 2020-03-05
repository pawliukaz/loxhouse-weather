<?php
declare(strict_types=1);

namespace App\Service;

use App\Client\MeteoClient;
use App\Entity\MeteoWeather;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MeteoService extends BaseService
{
    /**
     * @var MeteoClient
     */
    private $client;

    /**
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface|null $dispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(EntityManagerInterface $entityManager,MeteoClient $client, ?EventDispatcherInterface $dispatcher, ?LoggerInterface $logger)
    {
        parent::__construct($entityManager, $dispatcher, $logger);
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return MeteoWeather::class;
    }

    /**
     *
     */
    public function getMeteoData()
    {

    }
}
