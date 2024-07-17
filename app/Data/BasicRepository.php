<?php declare(strict_types=1);
namespace App\Data;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

abstract class BasicRepository
{
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_CODE = 'code';
    public const COL_DT_INS = 'dt_ins';
    public const COL_DT_UPD = 'dt_upd';
    public const COL_ENABLED = 'enabled';

    public function __construct(
        protected readonly Explorer $db
    ) {
    }

    abstract public function getTranslateTable(): array;

    public function insert(array $data): ?int
    {
        $this->db->table(static::TABLE)->insert($data);
        return (int) $this->db->getInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $row = $this->get($id);
        if ($row !== null) {
            $row->update($data);
            return true;
        }
        return false;
    }

    public function get(int $id): ?ActiveRow
    {
        return $this->db
            ->table(static::TABLE)
            ->where(self::COL_ENABLED, true)
            ->get($id);
    }

    public function delete(int $id, bool $safeDelete = true): bool
    {
        if ($safeDelete) {
            return $this->update($id, [BasicRepository::COL_ENABLED => false]);
        }

        $row = $this->get($id);
        if ($row !== null) {
            $row->delete();
            return true;
        }
        return false;
    }
}
