<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Presenters;

use App\Business\Controllers\GameController;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;
use App\Data\Map\MapEntity;
use App\Data\Map\MapManager;
use App\Data\Player\PlayerManager;
use App\Services\MapService;

final class GamePresenter extends SecuredPresenter
{
    private IslandEntity $islandEntity;

    public function __construct(
        private readonly MapService $mapService
        , private readonly IslandManager $islandManager
        , private readonly PlayerManager $playerManager
        , private readonly MapManager $mapManager
        , private readonly GameController $gameController
    ) {
        parent::__construct();
    }

    public function actionDefault(string $id): void
    {
        $this->islandEntity = $this->islandManager->getByCode($id);

//        bdump($this->mapService->calculate2dDistance(9, 6, 9, 9));
//        bdump($this->mapService->calculate3dDistance(9, 6, 9, 9));
    }

    public function renderDefault(string $id): void
    {
        $mapSettings = $this->islandEntity->getDataDecoded();
        $this->mapService->loadMap($this->islandEntity);

        $cellWidth = $mapSettings['imageWidth'] / $mapSettings['cols'];
        $cellHeight = $mapSettings['imageHeight'] / $mapSettings['rows'];

        $this->template->cellWidth = $cellWidth;
        $this->template->cellHeight = $cellHeight;
        $this->template->imageWidth = $mapSettings['imageWidth'];
        $this->template->imageHeight = $mapSettings['imageHeight'];
        $this->template->cells = $this->mapService->getMap();
        $this->template->tiles = $this->mapService->getMapTiles();
        $this->template->island = $this->islandEntity;

        $this->template->isAccountJoined = $this->gameController->isAccountJoined($this->accountEntity, $this->islandEntity);
        $this->template->players = $this->playerManager->getJoinedPlayers($this->islandEntity);
    }

    public function handleJoinIsland(string $id): void
    {
        $this->islandEntity = $this->islandManager->getByCode($id);
        $this->gameController->join($this->accountEntity, $this->islandEntity);

        $this->redrawControl('gameMap');
    }

    public function handleTileDetail(string $id, int $tile): void
    {
        /** @var MapEntity $m */
        $m = $this->mapManager->get($tile);

        $this->template->tileId = $m->getTitle();
        $this->payload->tileId = $m->getTitle();
        $this->redrawControl('gamePanel');
    }

    public function handleReload(): void
    {
        $this->redrawControl('gameMap');
    }
}
