<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    private const PER_PAGE = 10;

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
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $limit = self::PER_PAGE;
        $totalItems = $this->weatherService->getWeatherCount();
        $totalPages = max(1, (int)ceil($totalItems / $limit));
        $page = min($totalPages, max(1, $request->query->getInt('page', 1)));

        return $this->render('weather/index.html.twig', [
            'weather' => $this->weatherService->getWeatherData($page, $limit),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }
}

