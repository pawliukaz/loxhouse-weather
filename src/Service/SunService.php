<?php
declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Throwable;

class SunService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lon;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $sunBaseUrl
     * @param float $lat
     * @param float $lon
     * @param LoggerInterface $logger
     */
    public function __construct(string $sunBaseUrl, float $lat, float $lon, LoggerInterface $logger)
    {
        $this->lon = $lon;
        $this->lat = $lat;
        $this->client = new Client(['base_uri' => $sunBaseUrl]);
        $this->logger = $logger;
    }

    /**
     * @param float|null $lat
     * @param float|null $lon
     * @return array
     */
    public function getSunSetSunRiseTime(?float $lat, ?float $lon) : array
    {
        $uri = '/json?'.
            http_build_query(
                [
                    'lat'=> $lat?:$this->lat,
                    'lng'=> $lon?:$this->lon,
                    'formatted'=> 0
                ]
            );
        try {
            $response = $this->client->get($uri);
            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $exception) {
            $this->logger->error(
                $exception->getMessage(),
                [
                    'url' => $uri,
                    'body' => $exception->getResponse()?$exception->getResponse()->getBody()->getContents():null,
                    'code' => $exception->getResponse()->getStatusCode()
                ]
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['url' => $uri, 'code' => $exception->getCode()]);
        }

        return [];
    }
}
