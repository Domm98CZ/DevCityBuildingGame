<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Presenters;

use App\Data\Island\IslandManager;

final class HomepagePresenter extends SecuredPresenter
{
    public function __construct(
        private readonly IslandManager $islandManager
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
    }

    public function renderDefault(): void
    {
        $this->template->islands = $this->islandManager->getStartedIslands();
    }
}
