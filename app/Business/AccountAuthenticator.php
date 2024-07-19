<?php declare(strict_types=1);
namespace App\Business;

use App\Business\Enums\AccountType;
use App\Data\Account\AccountEntity;
use App\Data\Account\AccountManager;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final readonly class AccountAuthenticator implements Authenticator, IdentityHandler
{
    public function __construct(
        private AccountManager $accountManager,
        private Passwords $passwords
    ) {
    }

    public function authenticate(string $user, string $password): IIdentity
    {
        $accountEntity = $this->accountManager->getAccountByUsername($user);
        if (!$accountEntity instanceof AccountEntity) {
            throw new AuthenticationException('Account not exists.', self::IDENTITY_NOT_FOUND);
        }


        if (!$this->passwords->verify($password, $accountEntity->getPassword())) {
            throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        }

        if ($this->passwords->needsRehash($accountEntity->getPassword())) {
            $accountEntity->setPassword($this->passwords->hash($password));
            $accountEntity->save();
        }

        return new SimpleIdentity(
            $accountEntity->getId()
            , [$accountEntity->isAdmin() ? AccountType::ADMIN->getName() : AccountType::BASIC->getName()]
            , $accountEntity->__toArray()
        );
    }

    public function wakeupIdentity(IIdentity $identity): ?IIdentity
    {
        // TODO: Implement wakeupIdentity() method.
        return $identity;
    }

    public function sleepIdentity(IIdentity $identity): IIdentity
    {
        // TODO: Implement sleepIdentity() method.
        return $identity;
    }
}
