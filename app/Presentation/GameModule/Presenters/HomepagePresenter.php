<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Presenters;

use App\Data\Island\IslandManager;
use App\Services\MapService;

final class HomepagePresenter extends BasePresenter
{
    public function __construct(
        private readonly MapService $mapService
        , private readonly IslandManager $islandManager
    ) {

    }

    public function actionDetail(string $id): void
    {
        $island = $this->islandManager->getByCode($id);
        $this->mapService->loadMap($island);

        $cellWidth = $this->mapService->getCfg()[0] / $this->mapService->getCfg()[2];
        $cellHeight = $this->mapService->getCfg()[1] / $this->mapService->getCfg()[3];

        $this->template->cellWidth = $cellWidth;
        $this->template->cellHeight = $cellHeight;
        $this->template->cells = $this->mapService->getMap();
        $this->template->island = $island;

//        bdump($this->mapService->calculate2dDistance(9, 6, 9, 9));
//        bdump($this->mapService->calculate3dDistance(9, 6, 9, 9));
    }

    public function actionDefault(): void
    {
        $this->template->islands = $this->islandManager->getIslands();
    }
}
