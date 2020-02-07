<?php
declare(strict_types=1);

namespace App\Controller;

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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $weatherLogger)
    {
        $this->logger = $weatherLogger;
    }


    /**
     * @Route("/forecast/")
     * @param Request $request
     * @return Response
     */
    public function forecastAction(Request $request)
    {
        $this->logger->info('GET', $request->query->all());
        $this->logger->info('POST', $request->request->all());
        $this->logger->info('HEADER', $request->headers->all());
        return $this->render('weather/forecast.xml.twig', []);
    }
}
