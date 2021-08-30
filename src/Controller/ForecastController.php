<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\SunService;
use App\Service\WeatherService;
use DateTime;
use DateTimeZone;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/")
*/
class ForecastController extends Controller
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var WeatherService
     */
    protected $service;

    /**
     * @var SunService
     */
    protected $sunService;

    /**
     * @var float
     */
    private $lon;

    /**
     * @var float
     */
    private $lat;

    /**
     * @param LoggerInterface $weatherLogger
     * @param WeatherService $service
     * @param SunService $sunService
     */
    public function __construct(
        LoggerInterface $weatherLogger,
        WeatherService $service,
        SunService $sunService,
        float $lon,
        float $lat
    ) {
        $this->logger = $weatherLogger;
        $this->service = $service;
        $this->sunService = $sunService;
        $this->lon = $lon;
        $this->lat = $lat;
    }

    /**
     * @Route("/forecast/")
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function forecastAction(Request $request)
    {
        $coordinates = explode(',', $request->get('coord'));
        $sun = $this->sunService->getSunSetSunRiseTime((float)$coordinates[1], (float)$coordinates[0]);
        $sunRise = new DateTime();
        $sunSet = new DateTime();
        if (!empty($sun)) {
            $sunRise = new DateTime($sun['results']['sunrise']);
            $sunSet = new DateTime($sun['results']['sunset']);
        }
        $data = $this->service->getForecastData();
        if (0 === (int)$request->get('format')) {
            $response = new Response(
                '', 200,
                [
                    'Vary' => 'Accept-Encoding',
                    'Content-Type' => 'text/xml',
                    'Transfer-Encoding' => 'chunked'
                ]
            );
            return $this->render(
                'weather/forecast.xml.twig',
                [
                    'data' => $data,
                    'sunRise' => $sunRise,
                    'sunSet' => $sunSet,
                    'lon' => (float)$coordinates[0],
                    'lat' => (float)$coordinates[1]
                ],
                $response
            );
        }
        $response = new Response(
            '',
            200,
            [
                'Vary' => 'Accept-Encoding',
                'Content-Type' => 'text/plain',
                'Transfer-Encoding' => 'chunked'
            ]
        );
        $sunRise = clone $sunRise->setTimezone(new DateTimeZone('Europe/Vilnius'));
        $sunSet = clone $sunSet->setTimezone(new DateTimeZone('Europe/Vilnius'));
        return $this->render(
            'weather/forecast.csv.twig',
            [
                'data' => $data,
                'sunRise' => $sunRise,
                'sunSet' => $sunSet,
                'lon' => (float)$coordinates[0],
                'lat' => (float)$coordinates[1]
            ],
            $response
        );
    }
}
