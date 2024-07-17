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

    public function actionDefault(?string $id = null): void
    {
        if ($id !== null) {
            $this->mapService->setSeed($id);
        }

        $this->mapService->loadMap($this->islandManager->get(1));

        $cellWidth = $this->mapService->getCfg()[0] / $this->mapService->getCfg()[2];
        $cellHeight = $this->mapService->getCfg()[1] / $this->mapService->getCfg()[3];

        $this->template->cellWidth = $cellWidth;
        $this->template->cellHeight = $cellHeight;
        $this->template->cells = $this->mapService->getMap();
        $this->template->seed = $id;

        bdump($this->mapService->calculate2dDistance(9, 6, 9, 9));
        bdump($this->mapService->calculate3dDistance(9, 6, 9, 9));
    }
}
