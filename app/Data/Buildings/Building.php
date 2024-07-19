<?php declare(strict_types=1);
namespace App\Data\Buildings;

use App\Business\Enums\BuildingType;


// @TODO: dunno how fn
abstract readonly class Building
{
    /**
     * @param BuildingType $buildingType
     * @param int $level
     * @param string $name
     * @param Cost[] $costs
     */
    public function __construct(
        protected BuildingType $buildingType
        , protected int $level
        , protected string $name
        , protected array $costs = []
    ) {
    }

    public function getBuildingType(): BuildingType
    {
        return $this->buildingType;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return Cost[]
     */
    public function getCosts(): array
    {
        return $this->costs;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
