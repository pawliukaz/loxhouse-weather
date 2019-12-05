<?php

namespace App\Service;

use App\Entity\Weather;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WeatherService extends BaseService
{

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * WeatherService constructor.
     * @param EntityManagerInterface $entityManager
     * @param null|EventDispatcherInterface $dispatcher
     * @param null|LoggerInterface $logger
     * @param ClientInterface $client
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?EventDispatcherInterface $dispatcher,
        ?LoggerInterface $logger = null,
        ClientInterface $client
    ) {
        parent::__construct($entityManager, $dispatcher, $logger);
        $this->client = $client;
    }


    public function getWeatherForecast()
    {
        $response = $this->client
            ->request('GET', 'data/2.5/forecast?APPID=011c395d586e90c0b5cf3e34a143e442'.
                '&q=Kaunas&mode=json&units=metric&lang=lt');
        $value = json_decode($response->getBody()->getContents(), true);
        $weather = new Weather();
        $weather->setTook(new \DateTime());
        $weather->setWeather($value);
        $this->entityManager->persist($weather);
        $this->entityManager->flush();
    }
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Weather::class;
    }
}
