<?php declare(strict_types=1);
namespace App\Data\Account;

use App\Data\BasicEntity;

final class AccountEntity extends BasicEntity
{
    public function __construct(
        AccountManager $manager
        , ?int $id
        , private string $username
        , private string $password
        , private ?string $name
        , private string $email
        , private bool $admin
    ) {
        parent::__construct($manager, $id);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): AccountEntity
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): AccountEntity
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AccountEntity
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): AccountEntity
    {
        $this->email = $email;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): AccountEntity
    {
        $this->admin = $admin;
        return $this;
    }

    public function copy(): AccountEntity
    {
        return new AccountEntity(
            $this->getManager()
            , $this->getId()
            , $this->getUsername()
            , $this->getPassword()
            , $this->getName()
            , $this->getEmail()
            , $this->isAdmin()
        );
    }
}
