<?php declare(strict_types=1);
namespace App\Data\Buildings\Houses;

use App\Business\Enums\BuildingType;
use App\Business\Enums\ResourceType;
use App\Data\Buildings\Cost;
use App\Data\Buildings\StorageBuilding;

final readonly class WoodenHouse extends StorageBuilding
{
    public function __construct()
    {
        parent::__construct(
            BuildingType::HOUSE
            , 1
            , 'wooden_house'
            , [
                new Cost(ResourceType::WOOD, 10)
            ]
            , 10
        );
    }
}
