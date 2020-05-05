<?php
declare(strict_types=1);

namespace App\Service;

use App\Client\MeteoClient;
use App\Entity\MeteoWeather;
use App\Util\MeteoPlace;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MeteoService extends BaseService
{

    private const TYPE = 'long-term';
    /**
     * @var MeteoClient
     */
    private $client;

    /**
     * @param EntityManagerInterface $entityManager
     * @param MeteoClient $client
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
     * @param float $long
     * @param float $lat
     * @return string|null
     */
    private function callMeteoData(float $long, float $lat): ?string
    {
        try {
            $response = $this->client->get(
                sprintf('/v1/places/%s/forecasts/%s', MeteoPlace::getPlace($long, $lat), self::TYPE)
            );
            return $response->getBody()->getContents();
        } catch (BadResponseException $exception) {
            $this->logError(
                $exception->getMessage(),
                [
                    'code' => $exception->getCode(),
                    'responseBody' => $exception->getResponse()?$exception->getResponse()->getBody():null
                ]
            );
        }
        return null;
    }

    /**
     * @param float $long
     * @param float $lat
     * @throws Exception
     */
    public function downloadMeteoData(float $long, float $lat)
    {
        $meteoData = $this->callMeteoData($long, $lat);
        if ($meteoData) {
            $meteoData = json_decode($meteoData, true);
            $meteoModel = new MeteoWeather();
            $meteoModel->setPlace(MeteoPlace::getPlace($long, $lat));
            $meteoModel->setWeather($meteoData);
            $this->entityManager->persist($meteoModel);

        }
        // kaunas meteo data
        $meteoData = $this->callMeteoData(0, 0);
        if ($meteoData) {
            $meteoData = json_decode($meteoData, true);
            $meteoModel = new MeteoWeather();
            $meteoModel->setPlace(MeteoPlace::getPlace(0, 0));
            $meteoModel->setWeather($meteoData);
            $this->entityManager->persist($meteoModel);
        }
        $this->entityManager->flush();
    }
}
