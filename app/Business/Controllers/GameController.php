<?php declare(strict_types=1);
namespace App\Business\Controllers;

use App\Business\Enums\TerrainType;
use App\Data\Account\AccountEntity;
use App\Data\Island\IslandEntity;
use App\Data\Map\MapManager;
use App\Data\Player\PlayerEntity;
use App\Data\Player\PlayerManager;
use App\Services\MapService;
use Tracy\Debugger;

final class GameController
{
    public function __construct(
        private readonly PlayerManager $playerManager
        , private readonly MapService $mapService
        , private readonly MapManager $mapManager
    ) {

    }

    public function isAccountJoined(AccountEntity $accountEntity, IslandEntity $islandEntity): bool
    {
        return $this->playerManager->isAccountJoinedIsland($accountEntity, $islandEntity) instanceof PlayerEntity;
    }

    public function join(AccountEntity $accountEntity, IslandEntity $islandEntity): bool
    {
        try {
            if ($this->isAccountJoined($accountEntity, $islandEntity)) {
                return true;
            }

            $playerEntity = $this->playerManager->create($accountEntity, $islandEntity);
            $playerEntity->save();

            $this->mapService->loadMap($islandEntity);
            $coords = $this->mapService->findSafeEmptyCell();

            $tile = $this->mapManager->getIslandTileByCoords($islandEntity, $coords[0], $coords[1]);
            if ($tile->getType() === TerrainType::PLAINS->getTitle()) {
                $tile->setType(TerrainType::CITY->getTitle());
                $tile->setPlayerEntity($playerEntity);
                $tile->save();
            }
            return true;
        } catch (\Throwable $e) {
            Debugger::log($e);
        }

        return false;
    }
}
