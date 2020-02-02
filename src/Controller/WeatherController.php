<?php

namespace App\Controller;

use App\Service\WeatherService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    /**
     * @var WeatherService
     */
    protected $weatherService;

    /**
     * @param $weatherService
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * @Route("/", name="weather")
     */
    public function index(Request $request, LoggerInterface $logger): Response
    {
        $logger->error('POST', [$request->request->all()]);
        $logger->error('GET', [$request->query->all()]);
        $logger->error('HEAD', [$request->headers->all()]);
        return $this->render('weather/index.html.twig', [
            'weather' => $this->weatherService->getWeatherData()
        ]);
    }
}
