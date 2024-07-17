<?php declare(strict_types=1);
namespace App\Data\Map;

use App\Data\BasicRepository;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandRepository;
use Nette\Database\Table\Selection;

final class MapRepository extends BasicRepository
{
    public const TABLE = 'map';
    public const COL_TYPE = 'type';
    public const COL_ID_ISLAND = 'id_island';
    public const COL_X = 'x';
    public const COL_Y = 'y';

    public function getTranslateTable(): array
    {
        return [
            self::COL_ID            => self::COL_ID
            , self::COL_TYPE        => self::COL_TYPE
            , 'islandEntity'        => self::COL_ID_ISLAND
            , self::COL_X           => self::COL_X
            , self::COL_Y           => self::COL_Y
            , self::COL_DT_INS      => self::COL_DT_INS
            , self::COL_DT_UPD      => self::COL_DT_UPD
            , self::COL_ENABLED     => self::COL_ENABLED
        ];
    }

    public function getMapEntitySelection(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::COL_ENABLED, true);
    }

    public function getMapEntitySelectionByIslandEntity(IslandEntity $islandEntity): Selection
    {
        return $this->getMapEntitySelection()
            ->where(self::COL_ID_ISLAND, $islandEntity->getId());
    }
}
