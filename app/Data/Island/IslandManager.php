<?php declare(strict_types=1);
namespace App\Data\Island;

use App\Data\BasicManager;
use Nette\Database\Table\Selection;

final class IslandManager extends BasicManager
{
    public function __construct(
        IslandRepository $repository
    ) {
        parent::__construct($repository);
    }

    public function build(array $data): IslandEntity
    {
        $entity = new IslandEntity(
            $this
            , $data[IslandRepository::COL_ID] ?? null
            , $data[IslandRepository::COL_NAME]
            , $data[IslandRepository::COL_CODE]
            , $data[IslandRepository::COL_SEED]
            , $data[IslandRepository::COL_DATA]
            , $data[IslandRepository::COL_STARTED]
            , $data[IslandRepository::COL_FINISHED]
        );

        if (!empty($data[IslandRepository::COL_ID])) {
            $this->entity[$data[IslandRepository::COL_ID]] = $entity;
            $this->original[$data[IslandRepository::COL_ID]] = $entity->copy();
        }
        return $entity;
    }

    public function create(string $name, string $code, string $seed, string $data): IslandEntity
    {
        return new IslandEntity($this, null, $name, $code, $seed, $data, false, false);
    }

    public function getByCode(string $code): ?IslandEntity
    {
        $data = $this->repository->getIslandByCode($code)->fetch();
        if ($data !== null) {
            return $this->get($data[IslandRepository::COL_ID]);
        }

        return null;
    }

    /**
     * @return IslandEntity[]
     */
    public function getIslands(): array
    {
        $data = $this->repository->getIslands();
        $list = [];
        foreach ($data as $item) {
            $list[$item[IslandRepository::COL_ID]] = $this->build($item->toArray());
        }
        return $list;
    }
}
