<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\MeteoService;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MeteoDownloadCommand extends Command
{
    protected static $defaultName = 'app:meteo:download';

    /**
     * @var MeteoService;
     */
    protected $service;

    public function __construct(MeteoService $service)
    {
        parent::__construct();
        $this->service = $service;

    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOutput = new SymfonyStyle($input, $output);
        $startTime =  DateTime::createFromFormat('U.u', (string)microtime(TRUE));
        $this->service->downloadMeteoData( 23.8950, 54.9580);
        $endTime = DateTime::createFromFormat('U.u', (string)microtime(TRUE));
        $time = (int)$endTime->getTimestamp() - (int)$startTime->getTimestamp();
        $inputOutput->table(
            ['Task started.', 'Task finished.', 'Task done in time (s).'],
            [
                [
                    $startTime->format('Y-m-d H:i:s u'),
                    $endTime->format('Y-m-d H:i:s u'),
                    $time
                ]
            ]
        );
        $inputOutput->success('Done.');
    }


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Download meteo server data')
        ;
    }
}
