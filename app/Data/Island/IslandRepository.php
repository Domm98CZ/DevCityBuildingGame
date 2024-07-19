<?php declare(strict_types=1);
namespace App\Data\Island;

use App\Data\BasicRepository;
use Nette\Database\Table\Selection;

final class IslandRepository extends BasicRepository
{
    public const TABLE = 'island';
    public const COL_SEED = 'seed';
    public const COL_DATA = 'data';
    public const COL_STARTED = 'started';
    public const COL_FINISHED = 'finished';

    public function getTranslateTable(): array
    {
        return [
            self::COL_ID            => self::COL_ID
            , self::COL_NAME        => self::COL_NAME
            , self::COL_SEED        => self::COL_SEED
            , self::COL_CODE        => self::COL_CODE
            , self::COL_DATA        => self::COL_DATA
            , self::COL_STARTED     => self::COL_STARTED
            , self::COL_FINISHED    => self::COL_FINISHED
            , self::COL_DT_INS      => self::COL_DT_INS
            , self::COL_DT_UPD      => self::COL_DT_UPD
            , self::COL_ENABLED     => self::COL_ENABLED
        ];
    }

    public function getIslands(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::COL_ENABLED, true);
    }

    public function getStartedIslands(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::COL_ENABLED, true)
            ->where(self::COL_STARTED, true);
    }

    public function getIslandByCode(string $code): Selection
    {
        return $this->getIslands()
            ->where(self::COL_CODE, $code);
    }
}
