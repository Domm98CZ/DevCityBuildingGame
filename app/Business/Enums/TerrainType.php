<?php declare(strict_types=1);
namespace App\Business\Enums;

use Nette\Utils\ImageColor;

enum TerrainType
{
    case PLAINS;
    case FORREST;
    case LAKE;
    case MOUNTAINS;
    case SEA;
    case CITY; //@TODO: city levels dunno how for now

    public function getTitle(): string
    {
        return match($this) {
          self::PLAINS      => 'P',
          self::FORREST     => 'F',
          self::LAKE        => 'L',
          self::MOUNTAINS   => 'M',
          self::SEA         => 'S',
          self::CITY        => 'C'
        };
    }

    public function isClickable(): bool
    {
        return match ($this) {
            self::SEA, self::MOUNTAINS, self::LAKE, self::FORREST => false,
            default => true
        };
    }

    public function getColor(): ImageColor
    {
        return match($this) {
          self::PLAINS      => ImageColor::rgb(221, 232, 172),
          self::FORREST     => ImageColor::rgb(34, 139, 34),
          self::LAKE        => ImageColor::rgb(0, 0, 255),
          self::MOUNTAINS   => ImageColor::rgb(105, 105, 105),
          self::SEA         => ImageColor::rgb(0, 105, 148),
          self::CITY        => ImageColor::rgb(255, 140, 0),
        };
    }

    public function getImage(): ?string
    {
        return match($this) {
            self::PLAINS    => '/assets/images/map/plains.png',
//            self::FORREST   => '/assets/images/map/forest.png',
            self::SEA       => '/assets/images/map/sea.png',
            self::LAKE      => '/assets/images/map/lake.png',
            default         => null
        };
    }

    public function getTravelCosts(): float
    {
        return match($this) {
            self::LAKE          => 1.5,
            self::MOUNTAINS     => 2,
            default             => 1
        };
    }

    public static function getDefaultTypes(): array
    {
        return [
            self::FORREST,
            self::MOUNTAINS,
            self::LAKE
        ];
    }

    public static function getEnumByTitle(string $title): ?TerrainType
    {
        foreach (self::cases() as $case) {
            if ($case->getTitle() === $title) {
                return $case;
            }
        }

        return null;
    }
}
