<?php declare(strict_types=1);
namespace App\Data;

use App\Business\Exceptions\EntityNotFoundException;
use Tracy\Debugger;

abstract class BasicManager
{
    /** @var BasicEntity[] */
    protected array $entity;
    /** @var BasicEntity[] */
    protected array $original;

    public function __construct(
        protected readonly BasicRepository $repository
    ) {
    }

    public function get(int $id): BasicEntity
    {
        if (!isset($this->entity[$id])) {
            $data = $this->repository->get($id);
            if ($data !== null) {
                $this->build($data->toArray());
            }
        }

        return $this->entity[$id] ?? throw new EntityNotFoundException();
    }

    public function insert(BasicEntity $entity): ?int
    {
        if ($entity->getId() !== null) {
            return $entity->getId();
        }
        return $this->repository->insert($this->processEntityData($entity));
    }

    public function update(BasicEntity $entity): bool
    {
        if ($entity->getId() === null) {
            return false;
        }

        try {
            $dataForUpdate = $this->processEntityData($entity);
            bdump($dataForUpdate);
            if (!empty($dataForUpdate)) {
                $this->repository->update($entity->getId(), $dataForUpdate);
                $this->original[$entity->getId()] = $entity;
            }
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            return false;
        }

        return true;
    }


    private function processEntityData(BasicEntity $entity): array
    {
        $data = [];
        if ($entity->getId() !== null) {
            $original = $this->original[$entity->getId()] ?? null;
            if ($original instanceof BasicEntity) {
                $x = $original->__toArray();
                $y = $entity->__toArray();

                unset($x[BasicRepository::COL_ID]);
                unset($y[BasicRepository::COL_ID]);

                if ($x !== $y) {
                    $data = array_diff_assoc($y, $x);
                }
            }
        } else {
            $data = $entity->__toArray();
        }

        $databaseTranslateTable = $this->repository->getTranslateTable();
        if (!self::checkArrayForKeyAndValueMatch($databaseTranslateTable)) {
            foreach ($databaseTranslateTable as $property => $column) {
                if (array_key_exists($property, $data) && $property !== $column) {
                    $data[$column] = $data[$property];
                    unset($data[$property]);
                }
            }
        }
        unset($data[BasicRepository::COL_ID]);
        return $data;
    }

    private static function checkArrayForKeyAndValueMatch(array $array): bool
    {
        foreach ($array as $key => $value) {
            if ($key !== $value) {
                return false;
            }
        }
        return true;
    }
}
