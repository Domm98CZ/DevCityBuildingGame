<?php declare(strict_types=1);
namespace App\Presentation\Presenters;

use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->webTitle = 'Game';
        $this->template->cacheFix = '01010101';
    }
}
