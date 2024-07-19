<?php declare(strict_types=1);
namespace App\Data\Buildings;

use App\Business\Enums\BuildingType;

abstract readonly class StorageBuilding extends Building
{
    /**
     * @param BuildingType $buildingType
     * @param int $level
     * @param string $name
     * @param Cost[] $costs
     * @param int $capacity
     */
    public function __construct(
        protected BuildingType $buildingType
        , protected int $level
        , protected string $name
        , protected array $costs
        , protected int $capacity
    ) {
        parent::__construct($buildingType, $level, $name);
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }
}
