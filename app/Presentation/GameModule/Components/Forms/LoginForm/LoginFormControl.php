<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Components\Forms\LoginForm;

use App\Business\Controllers\AccountController;
use App\Business\Enums\MessageType;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

final class LoginFormControl extends Form
{
    public const INPUT_USERNAME = 'username';
    public const INPUT_PASSWORD = 'password';
    public const INPUT_SUBMIT = 'login';

    public function __construct(
       private readonly AccountController $accountController
        , private readonly Translator $translator
    ) {
        parent::__construct();

        $this->setTranslator($this->translator);
        $this->addText(self::INPUT_USERNAME, 'label.username');
        $this->addPassword(self::INPUT_PASSWORD, 'label.password');
        $this->addSubmit(self::INPUT_SUBMIT, 'button.login');
    }

    public function formSuccess(Form $form, ArrayHash $values): void
    {
        if ($this->getPresenter() === null) {
            throw new \RuntimeException('Component is initialized without presenter.');
        }

        try {
            $this->accountController->login($values[self::INPUT_USERNAME], $values[self::INPUT_PASSWORD]);
        } catch (AuthenticationException) {
            $this->getPresenter()->flashMessage($this->translator->translate('message.invalid_login'), MessageType::WARNING->getClass());
            return;
        }

        $this->getPresenter()->flashMessage($this->translator->translate('message.valid_login'), MessageType::SUCCESS->getClass());
        $this->getPresenter()->redirect(':Game:Homepage:default');
    }
}
