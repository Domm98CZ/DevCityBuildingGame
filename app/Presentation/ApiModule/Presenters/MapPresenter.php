<?php declare(strict_types=1);
namespace App\Presentation\ApiModule\Presenters;

use App\Data\Island\IslandManager;
use App\Services\MapService;
use Nette\Application\Responses\TextResponse;
use Throwable;
use Tracy\Debugger;

final class MapPresenter extends BasePresenter
{

    public function __construct(
        private readonly MapService $mapService
        , private readonly IslandManager $islandManager
    ) {

    }

//    public function actionData(?string $id = null): void
//    {
//        if ($id !== null) {
//            $this->mapService->setSeed($id);
//        }
//
//        $this->mapService->generateMap();
//
//        $this->getHttpResponse()->setCode(200);
//        $this->sendResponse(new TextResponse(Json::encode($this->getMap(), true)));
//    }

    public function actionImage(string $id): void
    {
        try {
            $this->mapService->generateImage($this->islandManager->getByCode($id))->send();
        } catch (Throwable $exception) {
            bdump($exception);
            Debugger::log($exception);
            $this->getHttpResponse()->setCode(404);
            $this->sendResponse(new TextResponse('not found'));
        }
        die;
    }
}
