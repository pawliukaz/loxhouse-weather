<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Util\Uuid;

/**
 * Class Weather
 * @package App\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\WeatherRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Weather
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $took;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    protected $weather;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTook(): DateTime
    {
        return $this->took;
    }

    /**
     * @param DateTime $took
     * @return $this
     */
    public function setTook(DateTime $took): self
    {
        $this->took = $took;

        return $this;
    }

    /**
     * @return array
     */
    public function getWeather(): array
    {
        return $this->weather;
    }

    /**
     * @param array $weather
     * @return $this
     */
    public function setWeather(array $weather): self
    {
        $this->weather = $weather;

        return $this;
    }
}
