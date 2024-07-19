<?php declare(strict_types=1);
namespace App\Business\Controllers;

use App\Business\Traits\AccountLoggedIn;
use App\Data\Account\AccountManager;
use Nette\Security\User;

final class AccountController
{
    use AccountLoggedIn;

    public function __construct(
        private readonly AccountManager $accountManager
        , private readonly User $user
    ) {

    }

    public function login(string $username, string $password): void
    {
        $this->user->login($username, $password);
    }
}
