<?php
declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
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
     * @var TraceableAdapter
     */
    private $sunCache;

    /**
     * @param string $sunBaseUrl
     * @param float $lat
     * @param float $lon
     * @param LoggerInterface $logger
     */
    public function __construct(string $sunBaseUrl, float $lat, float $lon, LoggerInterface $logger, RedisAdapter $cacheSun)
    {
        $this->lon = $lon;
        $this->lat = $lat;
        $this->client = new Client(['base_uri' => $sunBaseUrl]);
        $this->logger = $logger;
        $this->sunCache = $cacheSun;
    }

    /**
     * @param float|null $lat
     * @param float|null $lon
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getSunSetSunRiseTime(?float $lat, ?float $lon) : array
    {
        $item = $this->sunCache->getItem('sunset_sunrise_'.md5($lat.$lon));
        if (!$item->isHit()) {
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
            } catch (BadResponseException $exception) {
                $this->logger->error(
                    $exception->getMessage(),
                    [
                        'url' => $uri,
                        'body' => $exception->getResponse()?$exception->getResponse()->getBody()->getContents():null,
                        'code' => $exception->getResponse()->getStatusCode()
                    ]
                );
                return [];
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage(), ['url' => $uri, 'code' => $exception->getCode()]);
                return [];
            }

            $item->set(json_decode($response->getBody()->getContents(), true));
            $this->sunCache->save($item);
        }

        return $item->get();
    }
}
