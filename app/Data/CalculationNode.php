<?php declare(strict_types=1);
namespace App\Data;

final readonly class CalculationNode
{
    public function __construct(
        private int $x
        , private int $y
        , private float $cost = 0.0
        , private float $heuristic = 0.0
        , private ?CalculationNode $parent = null
    ) {
    }

    public function totalCost(): float
    {
        return $this->cost + $this->heuristic;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getHeuristic(): float
    {
        return $this->heuristic;
    }

    public function getParent(): ?CalculationNode
    {
        return $this->parent;
    }
}
