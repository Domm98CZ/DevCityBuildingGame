<?php declare(strict_types=1);
namespace App\Presentation\AdminModule\Presenters;

use App\Business\Exceptions\AccountIsNotAdminException;
use App\Business\Exceptions\AccountIsNotLoggedInException;
use App\Business\Traits\AccountLoggedIn;

abstract class SecuredPresenter extends BasePresenter
{
    use AccountLoggedIn;

    public function startup(): void
    {
        parent::startup();

        try {
            $this->adminAccountCheck();
        } catch (AccountIsNotLoggedInException) {
            $this->redirect(':Game:Auth:default');
        } catch (AccountIsNotAdminException) {
            $this->redirect(':Game:Homepage:default');
        }
    }
}
