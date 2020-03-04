<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * Class MeteoWeather
 * @package App\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\MeteoWeatherRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class MeteoWeather
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID");
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $took;

    /**
     * @var null|array
     * @ORM\Column(type="json")
     */
    private $weather;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->took = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MeteoWeather
     */
    public function setId(int $id): self
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
     * @return MeteoWeather
     */
    public function setTook(DateTime $took): self
    {
        $this->took = $took;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getWeather(): ?array
    {
        return $this->weather;
    }

    /**
     * @param array|null $weather
     * @return MeteoWeather
     */
    public function setWeather(?array $weather): self
    {
        $this->weather = $weather;
        return $this;
    }
}
