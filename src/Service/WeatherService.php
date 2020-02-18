<?php

namespace App\Service;

use App\Entity\Weather;
use App\Model\ForecastModel;
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
            $formattedData[] = $secondModel;
            $thirdModel = clone $secondModel;
            $thirdModel->setTimestamp($thirdModel->getTimestamp() + 3600);
            $formattedData[] = $thirdModel;
            $count++;
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
                return 1; #  1	Clear, cloudless sky (Loxone: Wolkenlos)
            case 801:
                return 8; #  2	Partly cloudy and few cirrus (Loxone: Heiter)
            case 802:
                return 12; # 12	Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 803:
                return 21; # 21	Mostly cloudy and cirrus (Loxone: Stark bewölkt)
            case 804:
                return 22; # 22	Overcast (Loxone: Bedeckt)

            case 701:	# Mist	mist
            case 711:	# Smoke	Smoke
            case 721:	# Haze	Haze
            case 731:	# Dust	sand/ dust whirls
            case 741:	# Fog	fog
            case 751:	# Sand	sand
            case 761:	# Dust	dust
            case 762:	# Ash	volcanic ash
            case 771:	# Squall	squalls
            case 781:	# Tornado	tornado
                return 18;	#Fog/low stratus clouds with cirrus (Loxone: Nebel)

            case 600:	# Snow	light snow
                return 24; #Overcast with snow (Loxone: Schneefall)
            case 601:	# Snow	Snow
                return 26; #Overcast with heavy snow (Loxone: Starker Schneefall)
            case 602:	# Snow	Heavy snow
                return 29;	#Storm with heavy snow (Loxone: Starker Schneeschauer)
            case 611:	# Snow	Sleet
            case 612:	# Snow	Light shower sleet
                return 24; #Overcast with snow (Loxone: Schneefall)
            case 613:	# Snow	Shower sleet
                return 34; #Overcast with light snow (Loxone: Leichter Schneeschauer)
            case 615:	# Snow	Light rain and snow
                return 32; #Mixed with snow showers (Loxone: Leichter Schneeschauer)
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
                return 22;
            case 502: #	heavy intensity rain
            case 503: #	very heavy rain
            case 504: #	extreme rain
                return 25;
            case 511: #	freezing rain
                return 35; # Overcast with mixture of snow and rain (Loxone: Schneeregen)
            case 520: #	light intensity shower rain
            case 521: #	shower rain
            case 522: # heavy intensity shower rain
            case 531: # ragged shower
                return 31; # 31	Mixed with showers (Loxone: Leichter Regenschauer)

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
                return 28; # Light rain, thunderstorms likely (Loxone: Gewitter)
            case 201:	#Thunderstorm	thunderstorm with rain
                return 27; #
            case 202:	#Thunderstorm	thunderstorm with heavy rain
                return 30; #Heavy rain, thunderstorms likely (Loxone: Kräftiges Gewitter)
            case 210:	#Thunderstorm	light thunderstorm
                return 12; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 211:	#Thunderstorm	thunderstorm
                return 12; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 212:	#Thunderstorm	heavy thunderstorm
                return 12; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 221:	#Thunderstorm	ragged thunderstorm
                return 12; # Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            case 230:	#Thunderstorm	thunderstorm with light drizzle
            case 231:	#Thunderstorm	thunderstorm with drizzle
            case 232:	#Thunderstorm	thunderstorm with heavy drizzle
                return 28; # Light rain, thunderstorms likely (Loxone: Gewitter)


            #  2	Clear, few cirrus (Loxone: Wolkenlos)
            #  3	Clear with cirrus (Loxone: Heiter)
            #  4	Clear with few low clouds (Loxone: Heiter)
            #  5	Clear with few low clouds and few cirrus (Loxone: Heiter)
            #  6	Clear with few low clouds and cirrus (Loxone: Heiter)
            #  7	Partly cloudy (Loxone: Heiter)
            #  8	Partly cloudy and few cirrus (Loxone: Heiter)
            #  9	Partly cloudy and cirrus (Loxone: Wolkig)
            # 10	Mixed with some thunderstorm clouds possible (Loxone: Wolkig)
            # 11	Mixed with few cirrus with some thunderstorm clouds possible (Loxone: Wolkig)
            # 12	Mixed with cirrus and some thunderstorm clouds possible (Loxone: Wolkig)
            # 13	Clear but hazy (Loxone: Wolkenlos)
            # 14	Clear but hazy with few cirrus (Loxone: Heiter)
            # 15	Clear but hazy with cirrus (Loxone: Heiter)
            # 16	Fog/low stratus clouds (Loxone: Nebel)
            # 17	Fog/low stratus clouds with few cirrus (Loxone: Nebel)
            # 18	Fog/low stratus clouds with cirrus (Loxone: Nebel)
            # 19	Mostly cloudy (Loxone: Stark bewölkt)
            # 20	Mostly cloudy and few cirrus (Loxone: Stark bewölkt)
            # 21	Mostly cloudy and cirrus (Loxone: Stark bewölkt)
            # 22	Overcast (Loxone: Bedeckt)
            # 23	Overcast with rain (Loxone: Regen)
            # 24	Overcast with snow (Loxone: Schneefall)
            # 25	Overcast with heavy rain (Loxone: Starker Regen)
            # 26	Overcast with heavy snow (Loxone: Starker Schneefall)
            # 27	Rain, thunderstorms likely (Loxone: Kräftiges Gewitter)
            # 28	Light rain, thunderstorms likely (Loxone: Gewitter)
            # 29	Storm with heavy snow (Loxone: Starker Schneeschauer)
            # 30	Heavy rain, thunderstorms likely (Loxone: Kräftiges Gewitter)
            # 31	Mixed with showers (Loxone: Leichter Regenschauer)
            # 32	Mixed with snow showers (Loxone: Leichter Schneeschauer)
            # 33	Overcast with light rain (Loxone: Leichter Regen)
            # 34	Overcast with light snow (Loxone: Leichter Schneeschauer)
            # 35	Overcast with mixture of snow and rain (Loxone: Schneeregen)
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
