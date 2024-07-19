<?php declare(strict_types=1);
namespace App\Data\Account;

use App\Data\BasicRepository;
use Nette\Database\Table\Selection;

final class AccountRepository extends BasicRepository
{
    public const TABLE = 'account';
    public const COL_USERNAME = 'username';
    public const COL_PASSWORD = 'password';
    public const COL_EMAIL = 'email';
    public const COL_ADMIN = 'admin';

    public function getTranslateTable(): array
    {
        return [
            self::COL_ID            => self::COL_ID
            , self::COL_USERNAME    => self::COL_USERNAME
            , self::COL_PASSWORD    => self::COL_PASSWORD
            , self::COL_NAME        => self::COL_NAME
            , self::COL_EMAIL       => self::COL_EMAIL
            , self::COL_ADMIN       => self::COL_ADMIN
            , self::COL_DT_INS      => self::COL_DT_INS
            , self::COL_DT_UPD      => self::COL_DT_UPD
            , self::COL_ENABLED     => self::COL_ENABLED
        ];
    }

    public function getAccounts(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::COL_ENABLED, true);
    }

    public function getAccountByUsername(string $username): Selection
    {
        return $this->getAccounts()
            ->where(self::COL_USERNAME, $username);
    }
}
