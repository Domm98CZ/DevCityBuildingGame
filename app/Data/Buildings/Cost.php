<?php declare(strict_types=1);
namespace App\Data\Buildings;

use App\Business\Enums\ResourceType;

final readonly class Cost
{
    public function __construct(
        private ResourceType $resourceType
        , private int $cost
    ) {

    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getCost(): int
    {
        return $this->cost;
    }
}
