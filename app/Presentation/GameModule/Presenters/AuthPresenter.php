<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Presenters;

use App\Presentation\GameModule\Components\Forms\LoginForm\LoginFormTrait;
use Nette\Security\User;

class AuthPresenter extends BasePresenter
{
    use LoginFormTrait;

    public function __construct(private readonly User $user)
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect(":Game:Homepage:default");
        }
    }

    public function actionLogout(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        }
        $this->redirect(":Game:Auth:default");
    }
}
