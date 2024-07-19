<?php declare(strict_types=1);
namespace App\Data\Map;

use App\Data\BasicManager;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;
use App\Data\Player\PlayerManager;
use Nette\Database\Table\Selection;

final class MapManager extends BasicManager
{
    public function __construct(
        MapRepository $repository
        , private readonly IslandManager $islandManager
        , private readonly PlayerManager $playerManager
    ) {
        parent::__construct($repository);
    }

    public function build(array $data): MapEntity
    {
        $entity = new MapEntity(
            $this
            , $data[MapRepository::COL_ID_PLAYER] !== null ? $this->playerManager->get($data[MapRepository::COL_ID_PLAYER]) : null
            , $this->islandManager->get($data[MapRepository::COL_ID_ISLAND])
            , $data[MapRepository::COL_ID] ?? null
            , $data[MapRepository::COL_TYPE]
            , $data[MapRepository::COL_X]
            , $data[MapRepository::COL_Y]
        );

        if (!empty($data[MapRepository::COL_ID])) {
            $this->entity[$data[MapRepository::COL_ID]] = $entity;
            $this->original[$data[MapRepository::COL_ID]] = $entity->copy();
        }
        return $entity;
    }

    public function create(IslandEntity $islandEntity, string $type, int $x, int $y): MapEntity
    {
        return new MapEntity($this, null, $islandEntity, null, $type, $x, $y);
    }

    public function getIslandTileByCoords(IslandEntity $islandEntity, int $x, int $y): ?MapEntity
    {
        $data = $this->repository->getIslandTileByCoords($islandEntity, $x, $y)->fetch();
        if ($data !== null) {
            return $this->get($data[MapRepository::COL_ID]);
        }
        return null;
    }

    public function getMapEntityByIsland(IslandEntity $islandEntity): Selection
    {
        return $this->repository->getMapEntitySelectionByIslandEntity($islandEntity);
    }
}
