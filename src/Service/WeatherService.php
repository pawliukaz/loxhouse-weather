<?php

namespace App\Service;

use App\Client\OpenWeatherClient;
use App\Entity\Weather;
use App\Model\ForecastModel;
use App\Repository\MeteoWeatherRepository;
use App\Repository\WeatherRepository;
use App\Util\MeteoPlace;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\ConnectException;
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

    /** @var MeteoWeatherRepository */
    protected $meteoRepository;

    /**
     * WeatherService constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface|null $weatherLogger
     * @param OpenWeatherClient $openWeatherApi
     * @param MeteoWeatherRepository $meteoRepository
     * @param null|EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $weatherLogger,
        OpenWeatherClient $openWeatherApi,
        MeteoWeatherRepository $meteoRepository,
        ?EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($entityManager, $dispatcher, $weatherLogger);
        $this->client = $openWeatherApi;
        $this->meteoRepository = $meteoRepository;
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
     * @param float|null $long
     * @param float|null $lat
     * @return ArrayCollection|ForecastModel[]
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getForecastData(?float $long = null, ?float $lat = null): ArrayCollection
    {
        /** @var ForecastModel[]|ArrayCollection $formattedData */
        $formattedData = new ArrayCollection();
        $meteoData = $this->meteoRepository->findLatest(MeteoPlace::getPlace((float)$long, (float)$lat));

        $data = $this->repository->findLatest();
        $data = current($data);
        $weather = $data->getWeather();
        if (null !== $meteoData) {
            $meteoWeather = $meteoData->getWeather();
            foreach ($meteoWeather['forecastTimestamps']  as $iteration => $meteoForecast) {
                $model = new ForecastModel();
                $model->setTimestamp(
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s', $meteoForecast['forecastTimeUtc'],
                        new DateTimeZone('UTC')
                    )->getTimestamp()
                )
                    ->setTemperature($meteoForecast['airTemperature'])
                    ->setWindDirection($meteoForecast['windDirection'])
                    ->setWindGust($meteoForecast['windGust']*3.6)
                    ->setWindSpeed($meteoForecast['windSpeed']*3.6)
                    ->setPrecipitation($meteoForecast['totalPrecipitation'])
                    ->setSeaLevelPressure($meteoForecast['seaLevelPressure'])
                    ->setLowClouds($meteoForecast['cloudCover'])
                    ->setMediumClouds($meteoForecast['cloudCover'])
                    ->setHighClouds($meteoForecast['cloudCover'])
                    ->setRelativeHumidity($meteoForecast['relativeHumidity'] ?? 0)
                    ->setCape(0)
                    ->setPictoCode($this->getMeteoPicoCode($meteoForecast['conditionCode']))
                ;
                $formattedData->add($model);
            }
        }

        $count = 0;

        foreach ($weather['list'] as $forecast) {
            $model = null;
            $count++;
            $model = $this->findModel($formattedData, $forecast["dt"] - $weather['city']['timezone']) ?? new ForecastModel();
            $model->setTimestamp($forecast["dt"] - $weather['city']['timezone'])
                ->setTemperature($model->getTemperature() ?? $forecast["main"]['temp'])
                ->setFeeledTemperature($forecast["main"]['feels_like'])
                ->setSeaLevelPressure($model->getSeaLevelPressure() ??$forecast["main"]['sea_level'])
                ->setRelativeHumidity($model->getRelativeHumidity() ?? $forecast["main"]['humidity'])
                ->setHighClouds($model->getHighClouds() ?? $forecast['clouds']['all'])
                ->setMediumClouds($model->getMediumClouds() ?? $forecast['clouds']['all'])
                ->setLowClouds( $model->getLowClouds() ?? $forecast['clouds']['all'])
                ->setWindSpeed($model->getWindSpeed() ?? $forecast['wind']['speed'])
                ->setWindDirection($model->getWindDirection() ?? $forecast['wind']['deg'])
                ->setPictoCode(
                    $model->getPictoCode() ?? $this->getPictoCode(
                        isset($forecast['weather'][0]['id'])?(int)$forecast['weather'][0]['id']:0
                    )
                )
            ;
            $formattedData->add($model);
            $secondModel = $this->findModel($formattedData, $model->getTimestamp() + 3600) ??
                (clone $model)->setTimestamp($model->getTimestamp() + 3600);
            $secondModel->setFeeledTemperature($forecast["main"]['feels_like'])
                ->setSeaLevelPressure($secondModel->getSeaLevelPressure() ?? $forecast["main"]['sea_level'])
                ->setRelativeHumidity($secondModel->getRelativeHumidity() ?? $forecast["main"]['humidity'])
                ->setHighClouds($secondModel->getHighClouds() ?? $forecast['clouds']['all'])
                ->setMediumClouds($secondModel->getMediumClouds() ?? $forecast['clouds']['all'])
                ->setLowClouds( $secondModel->getLowClouds() ?? $forecast['clouds']['all'])
                ->setWindSpeed($secondModel->getWindSpeed() ?? $forecast['wind']['speed'])
                ->setWindDirection($secondModel->getWindDirection() ?? $forecast['wind']['deg'])
            ;
            $formattedData->add($secondModel);

            $thirdModel = $this->findModel($formattedData, $secondModel->getTimestamp() + 3600) ??
                (clone $secondModel)->setTimestamp($secondModel->getTimestamp() + 3600);
            $thirdModel->setFeeledTemperature($forecast["main"]['feels_like'])
                ->setSeaLevelPressure($thirdModel->getSeaLevelPressure() ??$forecast["main"]['sea_level'])
                ->setRelativeHumidity($thirdModel->getRelativeHumidity() ?? $forecast["main"]['humidity'])
                ->setHighClouds($thirdModel->getHighClouds() ?? $forecast['clouds']['all'])
                ->setMediumClouds($thirdModel->getMediumClouds() ?? $forecast['clouds']['all'])
                ->setLowClouds( $thirdModel->getLowClouds() ?? $forecast['clouds']['all'])
                ->setWindSpeed($thirdModel->getWindSpeed() ?? $forecast['wind']['speed'])
                ->setWindDirection($thirdModel->getWindDirection() ?? $forecast['wind']['deg'])
            ;
            $formattedData->add($thirdModel);

        }
        return $formattedData;
    }

    /**
     * @param ArrayCollection $formattedData
     * @param int $timestamp
     * @return ForecastModel|null
     */
    private function findModel(ArrayCollection $formattedData, int $timestamp): ?ForecastModel
    {
        foreach ($formattedData as $formattedItem) {
            if ($formattedItem->getTimestamp() === $timestamp) {
                return $formattedItem;
            }
        }
        return null;
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
                return 22;
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
                return 16;

            case 600:	# Snow	light snow
                return 32; #Overcast with snow (Loxone: Schneefall)
            case 601:	# Snow	Snow
                return 24; #Overcast with heavy snow (Loxone: Starker Schneefall)
            case 602:	# Snow	Heavy snow
                return 26;	#Storm with heavy snow (Loxone: Starker Schneeschauer)
            case 611:	# Snow	Sleet
            case 612:	# Snow	Light shower sleet
            case 613:	# Snow	Shower sleet
            case 615:	# Snow	Light rain and snow
            case 616:	# Snow	Rain and snow
                return 35; # Overcast with mixture of snow and rain (Loxone: Schneeregen)
            case 620:	# Snow	Light shower snow
            case 621:	# Snow	Shower snow
                return 32;	#Mixed with snow showers (Loxone: Leichter Schneeschauer)
            case 622:	# Snow	Heavy shower snow
                return 29;	#Storm with heavy snow (Loxone: Starker Schneeschauer)


            //rain;
            case 500: # light rain
                return 33;
            case 501: #	moderate rain
                return 23;
            case 502: #	heavy intensity rain
            case 503: #	very heavy rain
            case 504: #	extreme rain
                return 25;
            case 511: #	freezing rain
                return 35; # Overcast with mixture of snow and rain (Loxone: Schneeregen)
            case 520: #	light intensity shower rain
                return 33;
            case 521: #	shower rain
            case 522: # heavy intensity shower rain
            case 531: # ragged shower
                return 31;

            case 300: # Drizzle	light intensity drizzle
            case 301: # Drizzle	drizzle
            case 302: # Drizzle	heavy intensity drizzle
            case 310: # Drizzle	light intensity drizzle rain
            case 311: # Drizzle	drizzle rain
            case 312: # Drizzle	heavy intensity drizzle rain
            case 313: # Drizzle	shower rain and drizzle
            case 314: # Drizzle	heavy shower rain and drizzle
            case 321: # Drizzle	shower drizzle
                return 31;


            //Thunder strom
            case 200:	#Thunderstorm	thunderstorm with light rain
            case 201:	#Thunderstorm	thunderstorm with rain
            case 202:	#Thunderstorm	thunderstorm with heavy rain
            case 210:	#Thunderstorm	light thunderstorm
            case 211:	#Thunderstorm	thunderstorm
                return 28; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 212:	#Thunderstorm	heavy thunderstorm
            case 221:	#Thunderstorm	ragged thunderstorm
            case 230:	#Thunderstorm	thunderstorm with light drizzle
            case 231:	#Thunderstorm	thunderstorm with drizzle
            case 232:	#Thunderstorm	thunderstorm with heavy drizzle
                return 30; # Light rain, thunderstorms likely (Loxone: Gewitter)

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
            # 15	Loxone: Clear
            # 16	Loxone: Fog
            # 17	Loxone: Fog
            # 18	Loxone: Fog
            # 19	Loxone: Heavy Cloud Cover
            # 20	Loxone: Heavy Cloud Cover
            # 21	Loxone: Heavy Cloud Cover
            # 22	Loxone: Cloudy
            # 23	Loxone: Rain
            # 24	Loxone: Snow
            # 25	Loxone: Heavy rain
            # 26	Loxone: Heavy snow
            # 27	Loxone: Strong thunderstorms
            # 28	Loxone: Thunderstorms
            # 29	Loxone: Heavy snow showers
            # 30	Loxone: Strong thunderstorms
            # 31	Loxone: Light showers
            # 32	Loxone: Light snow showers
            # 33	Loxone: Light rain
            # 34	Loxone: Light snow showers
            # 35    Loxone: sleet
        }
        return 1;
    }

    /**
     * @param string $id
     * @return int
     */
    private function getMeteoPicoCode(string $id): int
    {
        switch ($id) {
            case "clear": #giedra;
                return 1;
            case "isolated-clouds": #mažai debesuota;
                return 8;
            case "scattered-clouds": #debesuota su pragiedruliais;
                return 20;
            case "overcast": #debesuota;
                return 22;
            case "light-rain": #nedidelis lietus;
                return 33;
            case "moderate-rain": #lietus;
                return 23;
            case "heavy-rain": #smarkus lietus;
                return 25;
            case "sleet": #šlapdriba;
                return 35;
            case "light-snow": #nedidelis sniegas;
                return 32;
            case "moderate-snow": #sniegas;
                return 24;
            case "heavy-snow": #smarkus sniegas;
                return 29;
            case "fog": #rūkas;
                return 17;
            case "na": #oro sąlygos nenustatytos .
                return 17;
            default:
                return 1;
        }
    }



    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Weather::class;
    }
}
