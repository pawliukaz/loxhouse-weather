<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use App\Util\Uuid;

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
     * @var null|array
     * @ORM\Column(type="json")
     */
    private $weather;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $place;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->took = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MeteoWeather
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

    /**
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }

    /**
     * @param string $place
     * @return MeteoWeather
     */
    public function setPlace(string $place): self
    {
        $this->place = $place;
        return $this;
    }
}
