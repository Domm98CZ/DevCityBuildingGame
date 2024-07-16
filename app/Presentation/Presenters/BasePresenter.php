<?php declare(strict_types=1);

namespace App\Presentation\Presenters;

use Nette\Application\UI\Presenter;

/**
 * Class BasePresenter
 * @package App\Presentation\Presenters
 */
abstract class BasePresenter extends Presenter
{
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->webTitle = 'Game';
    }
}
