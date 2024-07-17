<?php declare(strict_types=1);
namespace App\Presentation\WebModule\Presenters;

use App\Presentation\ApiModule\Presenters\MapPresenter;

final class HomepagePresenter extends BasePresenter
{
    public function actionDefault(): void
    {
        //@TODO: temp iteration of map area
        $x = new MapPresenter();
        $x->generateMap();

        $cellWidth = $x->getCfg()[0] / $x->getCfg()[2];
        $cellHeight = $x->getCfg()[1] / $x->getCfg()[3];

        $this->template->cellWidth = $cellWidth;
        $this->template->cellHeight = $cellHeight;
        $this->template->cells = $x->getMap();
    }
}
