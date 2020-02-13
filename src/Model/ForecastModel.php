<?php
declare(strict_types=1);

namespace App\Model;

use DateTime;
use Exception;

class ForecastModel implements ModelInterface
{
    /**
     * @var float
     */
    private $temperature;

    /**
     * @var float
     */
    private $feeledTemperature;

    /**
     * @var float
     */
    private $windSpeed;

    /**
     * @var int
     */
    private $windDirection;

    /**
     * @var int
     */
    private $windGust;

    /**
     * @var int
     */
    private $lowClouds;

    /**
     * @var int
     */
    private $mediumClouds;

    /**
     * @var int
     */
    private $highClouds;

    /**
     * @var float
     */
    private $precipitation;

    /**
     * @var int
     */
    private $probabilityOfPrecip;

    /**
     * @var float
     */
    private $snowFraction;

    /**
     * @var int
     */
    private $seaLevelPressure;

    /**
     * @var int
     */
    private $relativeHumidity;

    /**
     * @var int
     */
    private $cape;

    /**
     * @var int
     */
    private $pictoCode;

    /**
     * @var int
     */
    private $radiation;

    /**
     * @var int;
     */
    private $timestamp;

    /**
     * @return string
     * @throws Exception
     */
    public function getLocalDate(): string
    {
        $datetime = new DateTime();

        if ($this->timestamp) {
            $datetime->setTimestamp($this->timestamp);

        }
        return $datetime->format('d.m.Y');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getWeekday(): string
    {
        $datetime = new DateTime();

        if ($this->timestamp) {
            $datetime->setTimestamp($this->timestamp);

        }
        return $datetime->format('D');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getLocalTime(): string
    {
        $datetime = new DateTime();

        if ($this->timestamp) {
            $datetime->setTimestamp($this->timestamp);

        }
        return $datetime->format('H');
    }

    /**
     * @return float
     */
    public function getFeeledTemperature(): float
    {
        return $this->feeledTemperature;
    }

    /**
     * @param float $feeledTemperature
     * @return ForecastModel
     */
    public function setFeeledTemperature(float $feeledTemperature): self
    {
        $this->feeledTemperature = $feeledTemperature;
        return $this;
    }

    /**
     * @return float
     */
    public function getWindSpeed(): float
    {
        return $this->windSpeed;
    }

    /**
     * @param float $windSpeed
     * @return ForecastModel
     */
    public function setWindSpeed(float $windSpeed): self
    {
        $this->windSpeed = $windSpeed;
        return $this;
    }

    /**
     * @return int
     */
    public function getWindDirection(): int
    {
        return $this->windDirection;
    }

    /**
     * @param int $windDirection
     * @return ForecastModel
     */
    public function setWindDirection(int $windDirection): self
    {
        $this->windDirection = $windDirection;
        return $this;
    }

    /**
     * @return int
     */
    public function getWindGust(): int
    {
        return $this->windGust;
    }

    /**
     * @param int $windGust
     * @return ForecastModel
     */
    public function setWindGust(int $windGust): self
    {
        $this->windGust = $windGust;
        return $this;
    }

    /**
     * @return int
     */
    public function getLowClouds(): int
    {
        return $this->lowClouds;
    }

    /**
     * @param int $lowClouds
     * @return ForecastModel
     */
    public function setLowClouds(int $lowClouds): self
    {
        $this->lowClouds = $lowClouds;
        return $this;
    }

    /**
     * @return int
     */
    public function getMediumClouds(): int
    {
        return $this->mediumClouds;
    }

    /**
     * @param int $mediumClouds
     * @return ForecastModel
     */
    public function setMediumClouds(int $mediumClouds): self
    {
        $this->mediumClouds = $mediumClouds;
        return $this;
    }

    /**
     * @return int
     */
    public function getHighClouds(): int
    {
        return $this->highClouds;
    }

    /**
     * @param int $highClouds
     * @return ForecastModel
     */
    public function setHighClouds(int $highClouds): self
    {
        $this->highClouds = $highClouds;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrecipitation(): float
    {
        return $this->precipitation;
    }

    /**
     * @param float $precipitation
     * @return ForecastModel
     */
    public function setPrecipitation(float $precipitation): self
    {
        $this->precipitation = $precipitation;
        return $this;
    }

    /**
     * @return int
     */
    public function getProbabilityOfPrecip(): int
    {
        return $this->probabilityOfPrecip;
    }

    /**
     * @param int $probabilityOfPrecip
     * @return ForecastModel
     */
    public function setProbabilityOfPrecip(int $probabilityOfPrecip): self
    {
        $this->probabilityOfPrecip = $probabilityOfPrecip;
        return $this;
    }

    /**
     * @return float
     */
    public function getSeaLevelPressure(): float
    {
        return $this->seaLevelPressure;
    }

    /**
     * @param int $seaLevelPressure
     * @return ForecastModel
     */
    public function setSeaLevelPressure(int $seaLevelPressure): self
    {
        $this->seaLevelPressure = $seaLevelPressure;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelativeHumidity(): int
    {
        return $this->relativeHumidity;
    }

    /**
     * @param int $relativeHumidity
     * @return ForecastModel
     */
    public function setRelativeHumidity(int $relativeHumidity): self
    {
        $this->relativeHumidity = $relativeHumidity;
        return $this;
    }

    /**
     * @return int
     */
    public function getCape(): int
    {
        return $this->cape;
    }

    /**
     * @param int $cape
     * @return ForecastModel
     */
    public function setCape(int $cape): self
    {
        $this->cape = $cape;
        return $this;
    }

    /**
     * @return int
     */
    public function getPictoCode(): int
    {
        return $this->pictoCode;
    }

    /**
     * @param int $pictoCode
     * @return ForecastModel
     */
    public function setPictoCode(int $pictoCode): self
    {
        $this->pictoCode = $pictoCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getRadiation(): int
    {
        return $this->radiation;
    }

    /**
     * @param int $radiation
     * @return ForecastModel
     */
    public function setRadiation(int $radiation): self
    {
        $this->radiation = $radiation;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     * @return ForecastModel
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * @param float $temperature
     * @return ForecastModel
     */
    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * @return float
     */
    public function getSnowFraction(): float
    {
        return $this->snowFraction;
    }

    /**
     * @param float $snowFraction
     * @return ForecastModel
     */
    public function setSnowFraction(float $snowFraction): self
    {
        $this->snowFraction = $snowFraction;
        return $this;
    }
}
