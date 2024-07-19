<?php declare(strict_types=1);
namespace App\Data\Account;

use App\Data\BasicManager;

final class AccountManager extends BasicManager
{
    public function __construct(
        AccountRepository $repository
    ) {
        parent::__construct($repository);
    }

    public function build(array $data): AccountEntity
    {
        $entity = new AccountEntity(
            $this
            , $data[AccountRepository::COL_ID] ?? null
            , $data[AccountRepository::COL_USERNAME]
            , $data[AccountRepository::COL_PASSWORD]
            , $data[AccountRepository::COL_NAME]
            , $data[AccountRepository::COL_EMAIL]
            , $data[AccountRepository::COL_ADMIN]
        );

        if (!empty($data[AccountRepository::COL_ID])) {
            $this->entity[$data[AccountRepository::COL_ID]] = $entity;
            $this->original[$data[AccountRepository::COL_ID]] = $entity->copy();
        }
        return $entity;
    }

    public function create(string $username, string $password, string $email, bool $isAdmin): AccountEntity
    {
        return new AccountEntity($this, null, $username, $password, null, $email, $isAdmin);
    }

    /**
     * @return AccountEntity[]
     */
    public function getAccounts(): array
    {
        $data = $this->repository->getAccounts();
        $list = [];
        foreach ($data as $item) {
            $list[$item[AccountRepository::COL_ID]] = $this->build($item->toArray());
        }
        return $list;
    }

    public function getAccountByUsername(string $username): ?AccountEntity
    {
        $data = $this->repository->getAccountByUsername($username)->fetch();
        if ($data !== null) {
            return $this->get($data[AccountRepository::COL_ID]);
        }
        return null;
    }
}
