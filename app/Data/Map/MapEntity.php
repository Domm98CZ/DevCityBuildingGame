<?php declare(strict_types=1);
namespace App\Data\Map;

use App\Data\BasicEntity;
use App\Data\Island\IslandEntity;
use App\Data\Player\PlayerEntity;

final class MapEntity extends BasicEntity
{
    public function __construct(
        MapManager $manager
        , private ?PlayerEntity $playerEntity
        , private IslandEntity $islandEntity
        , ?int $id
        , private string $type
        , private int $x
        , private int $y
    ) {
        parent::__construct($manager, $id);
    }

    public function getPlayerEntity(): ?PlayerEntity
    {
        return $this->playerEntity;
    }

    public function setPlayerEntity(?PlayerEntity $playerEntity): MapEntity
    {
        $this->playerEntity = $playerEntity;
        return $this;
    }

    public function getIslandEntity(): IslandEntity
    {
        return $this->islandEntity;
    }

    public function setIslandEntity(IslandEntity $islandEntity): MapEntity
    {
        $this->islandEntity = $islandEntity;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): MapEntity
    {
        $this->type = $type;
        return $this;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function setX(int $x): MapEntity
    {
        $this->x = $x;
        return $this;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setY(int $y): MapEntity
    {
        $this->y = $y;
        return $this;
    }

    public function getTitle(): string
    {
        return sprintf('[%dx%d] T:%s P:%s', $this->getX(), $this->getY(), $this->getType(), $this->getPlayerEntity()?->getAccountEntity()->getId());
    }

    public function copy(): MapEntity
    {
        return new MapEntity(
            $this->getManager()
            , $this->getPlayerEntity()
            , $this->getIslandEntity()
            , $this->getId()
            , $this->getType()
            , $this->getX()
            , $this->getY()
        );
    }
}
