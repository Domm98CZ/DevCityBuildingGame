<?php declare(strict_types=1);
namespace App\Data\Buildings\WareHouses;

use App\Business\Enums\BuildingType;
use App\Business\Enums\ResourceType;
use App\Data\Buildings\Cost;
use App\Data\Buildings\StorageBuilding;

final readonly class StoneWareHouse extends StorageBuilding
{
    public function __construct()
    {
        parent::__construct(
            BuildingType::WAREHOUSE
            , 2
            , 'stone_warehouse'
            , [
                new Cost(ResourceType::WOOD, 50),
                new Cost(ResourceType::STONE, 200)
            ]
            , 100
        );
    }
}
