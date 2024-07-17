<?php declare(strict_types=1);
namespace App\Presentation\ApiModule\Presenters;

use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;
use App\Data\Map\MapManager;
use App\Data\Map\MapRepository;
use App\Services\MapService;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\Json;
use Throwable;
use Tracy\Debugger;

final class MapPresenter extends BasePresenter
{

    public function __construct(
        private readonly MapService $mapService
        , private readonly IslandManager $islandManager
    ) {

    }

    public function actionData(?string $id = null): void
    {
        if ($id !== null) {
            $this->mapService->setSeed($id);
        }

        $this->mapService->generateMap();

        $this->getHttpResponse()->setCode(200);
        $this->sendResponse(new TextResponse(Json::encode($this->getMap(), true)));
    }

    public function actionImage(?string $id = null): void
    {
        try {
            if ($id !== null) {
                $this->mapService->setSeed($id);
            }

            $this->mapService->generateImage($this->islandManager->get(1))->send();
        } catch (Throwable $exception) {
            bdump($exception);
            Debugger::log($exception);
            $this->getHttpResponse()->setCode(404);
            $this->sendResponse(new TextResponse('not found'));
        }
        die;
    }
}
