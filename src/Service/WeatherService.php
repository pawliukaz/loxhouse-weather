<?php

namespace App\Service;

use App\Entity\Weather;
use App\Repository\WeatherRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WeatherService extends BaseService
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /** @var WeatherRepository */
    protected $repository;

    /**
     * WeatherService constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $weatherBaseUrl
     * @param null|EventDispatcherInterface $dispatcher
     * @param LoggerInterface|null $weatherLogger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $weatherBaseUrl,
        LoggerInterface $weatherLogger,
        ?EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($entityManager, $dispatcher, $weatherLogger);
        $this->client = new Client(['base_uri' => $weatherBaseUrl]);
    }

    /**
     * @throws GuzzleException
     */
    public function getWeatherForecast()
    {
        try {
            $response = $this->client
                ->request('GET', 'data/2.5/forecast?APPID=011c395d586e90c0b5cf3e34a143e442'.
                    '&q=Kaunas&mode=json&units=metric&lang=lt');
        } catch (ConnectException $exception) {
            $this->logError($exception->getMessage(), [ $exception->getRequest()->getRequestTarget()]);
            return;
        }

        $value = json_decode($response->getBody()->getContents(), true);
        $weather = new Weather();
        $weather->setTook(new DateTime());
        $weather->setWeather($value);
        $this->entityManager->persist($weather);
        $this->entityManager->flush();
        $this->logDebug(
            'Weather data is crawled to database.',
            ['data' => $weather->getWeather()]
        );
        $this->logInfo(
            'Weather data is crawled to database.',
            ['date' => (new DateTime())->format('Y-m-d H:i:s')]
        );
    }

    /**
     * @return Weather[]
     */
    public function getWeatherData()
    {
        return $this->repository->findLatest();
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Weather::class;
    }
}
