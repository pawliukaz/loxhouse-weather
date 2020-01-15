<?php

namespace App\Command;

use App\Service\WeatherService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WeatherDownloadCommand extends Command
{
    protected static $defaultName = 'app:weather:download';

    /**
     * @var WeatherService;
     */
    protected $service;

    /**
     * @param WeatherService $service
     */
    public function __construct(WeatherService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Download weather open server')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOutput = new SymfonyStyle($input, $output);
        try {
            $this->service->getWeatherForecast();
        } catch (Exception $exception) {
            $inputOutput->error($exception->getMessage());
        }

        $inputOutput->success('Done.');
    }
}
