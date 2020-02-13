<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    /**
     * @param $number
     * @return float
     */
    public function formatWind($number)
    {
        $number = ($number * 18)/5;

        return round($number, 2);
    }
}
