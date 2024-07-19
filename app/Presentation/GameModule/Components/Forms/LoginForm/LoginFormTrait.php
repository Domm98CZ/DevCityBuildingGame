<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Components\Forms\LoginForm;

trait LoginFormTrait
{
    private ILoginFormFactory $loginFormFactory;

    public function injectLoginForm(ILoginFormFactory $loginFormFactory): void
    {
        $this->loginFormFactory = $loginFormFactory;
    }

    public function createComponentLoginForm(): LoginFormControl
    {
        $form = $this->loginFormFactory->create();
        $form->onSuccess[] = [$form, 'formSuccess'];
        return $form;
    }
}
