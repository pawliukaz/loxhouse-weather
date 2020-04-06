<?php

namespace App\Service;

use App\Client\OpenWeatherClient;
use App\Entity\Weather;
use App\Model\ForecastModel;
use App\Repository\WeatherRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WeatherService extends BaseService
{
    /**
     * @var OpenWeatherClient
     */
    protected $client;

    /** @var WeatherRepository */
    protected $repository;

    /**
     * WeatherService constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface|null $weatherLogger
     * @param null|EventDispatcherInterface $dispatcher
     * @param OpenWeatherClient $openWeatherApi
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $weatherLogger,
        OpenWeatherClient $openWeatherApi,
        ?EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($entityManager, $dispatcher, $weatherLogger);
        $this->client = $openWeatherApi;
    }

    /**
     * @throws Exception
     */
    public function getWeatherForecast(): void
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
    public function getWeatherData(): array
    {
        return $this->repository->findLatest();
    }

    /**
     * @return array|ForecastModel[]
     */
    public function getForecastData(): array
    {
        $formattedData = [];
        $data = $this->repository->findLatest();
        $data = current($data);
        $weather = $data->getWeather();
        $count = 0;
        foreach ($weather['list'] as $forecast) {
            $count++;
            $model = new ForecastModel();
            $model->setTimestamp($forecast["dt"])
                ->setTemperature($forecast["main"]['temp'])
                ->setFeeledTemperature($forecast["main"]['feels_like'])
                ->setSeaLevelPressure($forecast["main"]['sea_level'])
                ->setRelativeHumidity($forecast["main"]['humidity'])
                ->setHighClouds($forecast['clouds']['all'])
                ->setMediumClouds($forecast['clouds']['all'])
                ->setLowClouds($forecast['clouds']['all'])
                ->setWindSpeed($forecast['wind']['speed'])
                ->setWindDirection($forecast['wind']['deg'])
                ->setWindGust(0) // we do not have it
                ->setPrecipitation(0) // we do not have it
                ->setProbabilityOfPrecip(0) // we do not have it
                ->setRadiation(0) // we do not have it
                ->setSnowFraction(0) //we do not have it
                ->setCape(0)
                ->setPictoCode(
                    $this->getPictoCode(
                        isset($forecast['weather'][0]['id'])?(int)$forecast['weather'][0]['id']:0
                    )
                )
            ;
            $formattedData[] = $model;

            $secondModel = clone $model;
            $secondModel->setTimestamp($secondModel->getTimestamp() + 3600);
            $secondModel->setPictoCode(++$count);
            $formattedData[] = $secondModel;
            $thirdModel = clone $secondModel;
            $thirdModel->setTimestamp($thirdModel->getTimestamp() + 3600);
            $thirdModel->setPictoCode(++$count);
            $formattedData[] = $thirdModel;

        }
        return $formattedData;
    }

    /**
     * @param int $id
     * @return int
     */
    private function getPictoCode(int $id): int
    {

        if (0 === $id) {
            return 1;
        }
        switch ($id) {
            case 800:
                return 1;
            case 801:
                return 2;
            case 802:
            case 803:
                return 20;
            case 804:
                return 23;

            case 701:
            case 711:
            case 721:
            case 731:
            case 741:
            case 751:
            case 761:
            case 762:
            case 771:
            case 781:
                return 17;

            case 600:	# Snow	light snow
                return 33; #Overcast with snow (Loxone: Schneefall)
            case 601:	# Snow	Snow
                return 25; #Overcast with heavy snow (Loxone: Starker Schneefall)
            case 602:	# Snow	Heavy snow
                return 27;	#Storm with heavy snow (Loxone: Starker Schneeschauer)
            case 611:	# Snow	Sleet
            case 612:	# Snow	Light shower sleet
            case 613:	# Snow	Shower sleet
            case 615:	# Snow	Light rain and snow
                return 36; #Overcast with snow (Loxone: Schneefall)
            case 616:	# Snow	Rain and snow
                return 35; # Overcast with mixture of snow and rain (Loxone: Schneeregen)
            case 620:	# Snow	Light shower snow
            case 621:	# Snow	Shower snow
                return 33;	#Mixed with snow showers (Loxone: Leichter Schneeschauer)
            case 622:	# Snow	Heavy shower snow
                return 30;	#Storm with heavy snow (Loxone: Starker Schneeschauer)


            //rain;
            case 500: # light rain
                return 34;
            case 501: #	moderate rain
                return 24;
            case 502: #	heavy intensity rain
            case 503: #	very heavy rain
            case 504: #	extreme rain
                return 26;
            case 511: #	freezing rain
                return 36; # Overcast with mixture of snow and rain (Loxone: Schneeregen)
            case 520: #	light intensity shower rain
                return 32;
            case 521: #	shower rain
            case 522: # heavy intensity shower rain
            case 531: # ragged shower
                return 34;

            case 300: # Drizzle	light intensity drizzle
            case 301: # Drizzle	drizzle
            case 302: # Drizzle	heavy intensity drizzle
            case 310: # Drizzle	light intensity drizzle rain
            case 311: # Drizzle	drizzle rain
            case 312: # Drizzle	heavy intensity drizzle rain
            case 313: # Drizzle	shower rain and drizzle
            case 314: # Drizzle	heavy shower rain and drizzle
            case 321: # Drizzle	shower drizzle
                return 28;


            //Thunder strom
            case 200:	#Thunderstorm	thunderstorm with light rain
            case 201:	#Thunderstorm	thunderstorm with rain
            case 202:	#Thunderstorm	thunderstorm with heavy rain
            case 210:	#Thunderstorm	light thunderstorm
            case 211:	#Thunderstorm	thunderstorm
                return 29; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 212:	#Thunderstorm	heavy thunderstorm
            case 221:	#Thunderstorm	ragged thunderstorm
            case 230:	#Thunderstorm	thunderstorm with light drizzle
            case 231:	#Thunderstorm	thunderstorm with drizzle
            case 232:	#Thunderstorm	thunderstorm with heavy drizzle
                return 28; # Light rain, thunderstorms likely (Loxone: Gewitter)

            #  1	Loxone: Clear sky
            #  2	Loxone: Clear
            #  3	Loxone: Clear
            #  4	Loxone: Clear
            #  5	Loxone: Clear
            #  6	Loxone: Clear
            #  7	Loxone: Partly Cloudy
            #  8	Loxone: Partly Cloudy
            #  9	Loxone: Partly Cloudy
            # 10	Loxone: Partly Cloudy
            # 11	Loxone: Partly Cloudy
            # 12	Loxone: Partly Cloudy
            # 13	Loxone: Clear sky
            # 14	Loxone: Clear
            # 15	Loxone: Sleet
            # 16	Loxone: Clear
            # 17	Loxone: Fog
            # 18	Loxone: Fog
            # 19	Loxone: Fog
            # 20	Loxone: Heavy Cloud Cover
            # 21	Loxone: Heavy Cloud Cover
            # 22	Loxone: Heavy Cloud Cover
            # 23	Loxone: Cloudy
            # 24	Loxone: Rain
            # 25	Loxone: Snow
            # 26	Loxone: Heavy rain
            # 27	Loxone: Heavy snow
            # 28	Loxone: Strong thunderstorms
            # 29	Loxone: Thunderstorms
            # 30	Loxone: Heavy snow showers
            # 31	Loxone: Strong thunderstorms
            # 32	Loxone: Light showers
            # 33	Loxone: Light snow showers
            # 34	Loxone: Light rain
            # 35	Loxone: Light snow showers
            # 36    Loxone: sleet
        }
        return 1;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Weather::class;
    }
}
