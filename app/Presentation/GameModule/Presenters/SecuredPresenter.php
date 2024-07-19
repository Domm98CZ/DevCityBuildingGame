<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Presenters;

use App\Business\Exceptions\AccountIsNotLoggedInException;
use App\Business\Traits\AccountLoggedIn;

abstract class SecuredPresenter extends BasePresenter
{
    use AccountLoggedIn;

    public function startup(): void
    {
        parent::startup();

        try {
            $this->basicAccountCheck();
        } catch (AccountIsNotLoggedInException) {
            $this->redirect(':Game:Auth:default');
        }
    }
}
