<?php

declare(strict_types=1);

namespace App\Util;

class MeteoPlace
{
    public const DOMEIKAVA = 'domeikava';
    public const KAUNAS = 'kaunas';

    /**
     * @param float $long
     * @param float $lat
     * @return string
     */
    public static function getPlace(float $long, float $lat): string
    {
        if ($long >= 23.88112 && $long <= 23.95263 && $lat >= 54.95049 && $lat <= 54.97910) {
            return self::DOMEIKAVA;
        }

        return self::KAUNAS;
    }
}
