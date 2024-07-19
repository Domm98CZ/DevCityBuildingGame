<?php declare(strict_types=1);
namespace App\Data\Buildings\WareHouses;

use App\Business\Enums\BuildingType;
use App\Business\Enums\ResourceType;
use App\Data\Buildings\Cost;
use App\Data\Buildings\StorageBuilding;

final readonly class IronWareHouse extends StorageBuilding
{
    public function __construct()
    {
        parent::__construct(
            BuildingType::WAREHOUSE
            , 3
            , 'iron_warehouse'
            , [
                new Cost(ResourceType::WOOD, 200),
                new Cost(ResourceType::STONE, 50),
                new Cost(ResourceType::IRON, 200),
            ]
            , 1000
        );
    }
}
