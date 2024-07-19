<?php declare(strict_types=1);
namespace App\Business\Traits;

use App\Business\Exceptions\AccountIsNotAdminException;
use App\Business\Exceptions\AccountIsNotLoggedInException;
use App\Business\Exceptions\EntityNotFoundException;
use App\Data\Account\AccountEntity;
use App\Data\Account\AccountManager;
use Nette\Security\User;

trait AccountLoggedIn
{
    private readonly AccountManager $accountManager;
    private readonly User $user;
    protected AccountEntity $accountEntity;

    public function injectUserLoggedIn(AccountManager $accountManager, User $user): void
    {
        $this->accountManager = $accountManager;
        $this->user = $user;
    }

    /**
     * @return AccountEntity
     * @throws AccountIsNotLoggedInException
     */
    public function basicAccountCheck(): AccountEntity
    {
        if (!isset($this->user) || !$this->user->isLoggedIn()) {
            $this->user->logout(true);
            throw new AccountIsNotLoggedInException();
        }

        if (isset($this->accountEntity)) {
            return $this->accountEntity;
        }

        try {
            /** @var AccountEntity $accountEntity */
            $this->accountEntity = $this->accountManager->get($this->user->getId());
        } catch (EntityNotFoundException) {
            $this->user->logout(true);
            throw new AccountIsNotLoggedInException();
        }

        return $this->accountEntity;
    }

    /**
     * @return AccountEntity
     * @throws AccountIsNotAdminException
     * @throws AccountIsNotLoggedInException
     */
    public function adminAccountCheck(): AccountEntity
    {
        $accountEntity = $this->basicAccountCheck();
        bdump($accountEntity);

//        if (!$accountEntity->isAdmin()) {
//            throw new AccountIsNotAdminException();
//        }

        return $accountEntity;
    }
}
