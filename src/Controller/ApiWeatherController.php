<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiWeatherController extends AbstractController
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
     * @Route("/api/weather", name="weather")
     */
    public function index(): Response
    {
        
    }
}