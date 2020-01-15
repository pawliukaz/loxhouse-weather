<?php

namespace App\Service;

use App\Entity\Weather;
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

    /**
     * WeatherService constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $weatherBaseUrl
     * @param null|EventDispatcherInterface $dispatcher
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $weatherBaseUrl,
        ?EventDispatcherInterface $dispatcher,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($entityManager, $dispatcher, $logger);
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
    }
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Weather::class;
    }
}
