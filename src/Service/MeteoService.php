<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\MeteoWeather;

class MeteoService extends BaseService
{

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return MeteoWeather::class;
    }
}
