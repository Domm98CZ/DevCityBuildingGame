<?php declare(strict_types=1);
namespace App\Data\Player;

use App\Data\Account\AccountEntity;
use App\Data\BasicRepository;
use App\Data\Island\IslandEntity;
use Nette\Database\Table\Selection;

final class PlayerRepository extends BasicRepository
{
    public const TABLE = 'player';
    public const COL_ID_ACCOUNT = 'id_account';
    public const COL_ID_ISLAND = 'id_island';

    public function getTranslateTable(): array
    {
        return [
            self::COL_ID            => self::COL_ID
            , 'accountEntity'       => self::COL_ID_ACCOUNT
            , 'islandEntity'        => self::COL_ID_ISLAND
            , self::COL_DT_INS      => self::COL_DT_INS
            , self::COL_DT_UPD      => self::COL_DT_UPD
            , self::COL_ENABLED     => self::COL_ENABLED
        ];
    }

    public function getPlayersSelection(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::COL_ENABLED, true);
    }

    public function getPlayersIslandSelection(IslandEntity $islandEntity): Selection
    {
        return $this->getPlayersSelection()
            ->where(self::COL_ID_ISLAND, $islandEntity->getId());
    }

    public function isAccountJoinedIsland(AccountEntity $accountEntity, IslandEntity $islandEntity): Selection
    {
        return $this->getPlayersSelection()
            ->where(self::COL_ID_ACCOUNT, $accountEntity->getId())
            ->where(self::COL_ID_ISLAND, $islandEntity->getId());
    }
}
