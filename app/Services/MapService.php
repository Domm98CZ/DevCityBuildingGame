<?php declare(strict_types=1);
namespace App\Services;

use App\Business\Enums\TerrainType;
use App\Data\CalculationNode;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;
use App\Data\Map\MapEntity;
use App\Data\Map\MapManager;
use App\Data\Map\MapRepository;
use GdImage;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\Json;

final class MapService
{
    private int $imageWidth = 800;
    private int $imageHeight = 800;
    private int $rows = 25;
    private int $cols = 25;
    private array $directions = [
        [-1, 0], [1, 0], [0, -1], [0, 1], // N, S, W, E
        [-1, -1], [-1, 1], [1, -1], [1, 1] // NW, NE, SW, SE
    ];
    private array $map;
    /** @var MapEntity[][][] */
    private array $mapTiles;
    private float $forrestProbability = 0.6;
    private float $mountainProbability = 0.4;
    private float $lakeProbability = 0.25;
    private float $emptyProbability = 0.95;
    private float $seaProbability = 0.5;
    private int $emptyVillages = 50;
    private string $seed = 'dommDevGame';

    public function __construct(
        private readonly MapManager $mapManager
        , private readonly IslandManager $islandManager
    ) {
    }

    public function generateImage(IslandEntity $islandEntity): Image
    {
        $bgColorRgb = ImageColor::rgb(0, 0, 0);
        $fnColorRgb = ImageColor::rgb(255, 255, 255);
        $txColorRgb = ImageColor::rgb(255, 255, 255);

        $image = Image::fromBlank($this->imageWidth, $this->imageHeight, $bgColorRgb);
        $im = $image->getImageResource();

        $lnColor = imagecolorallocate($im, $fnColorRgb->red, $fnColorRgb->green, $fnColorRgb->blue);
        $txColor = imagecolorallocate($im, $txColorRgb->red, $txColorRgb->green, $txColorRgb->blue);

        // výpočet vzdálenosti mezi řádky a sloupci
        $rowHeight = (int) ceil($this->imageHeight / $this->rows);
        $colWidth = (int) ceil($this->imageWidth / $this->cols);

        // dynamické nastavení velikosti fontu
        $minCellSize = min($rowHeight, $colWidth);
        $fontSize = max(1, min(5, intval($minCellSize / 10)));

        $this->loadMap($islandEntity);

        // vykreslení písmen do mapy
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                $char = $this->map[$i][$j];

                $terrainTypeEnum = TerrainType::getEnumByTitle($char);
                if ($terrainTypeEnum->getImage() !== null) {
                    $this->drawObjectImage($im, $terrainTypeEnum->getImage(), $j * $colWidth,  $i * $rowHeight, $colWidth, $rowHeight);
                } else {
                    $tileColor = $terrainTypeEnum->getColor();
                    $bgColor = imagecolorallocate($im, $tileColor->red, $tileColor->green, $tileColor->blue);
                    imagefilledrectangle($im, $j * $colWidth, $i * $rowHeight, ($j + 1) * $colWidth, ($i + 1) * $rowHeight, $bgColor);
                }

//                if ($char !== TerrainType::PLAINS->getTitle()) {
//                    $x = (int) ceil($j * $colWidth + ($colWidth / 2) - (imagefontwidth($fontSize) / 2));
//                    $y = (int) ceil($i * $rowHeight + ($rowHeight / 2) - (imagefontheight($fontSize) / 2));
//                    imagestring($im, $fontSize, $x, $y, $char, $txColor);
//                }
            }
        }

        // doplnění grid čar do mapy
//        for ($i = 1; $i < $this->cols; $i++) {
//            imageline($im, $i * $colWidth, 0, $i * $colWidth, $this->imageHeight, $lnColor);
//        }
//
//        for ($i = 1; $i < $this->rows; $i++) {
//            imageline($im, 0, $i * $rowHeight, $this->imageWidth, $i * $rowHeight, $lnColor);
//        }
        return $image;
    }


    public function setSeed(string $seed): void
    {
        $this->seed = $seed;
    }

    public function loadMap(IslandEntity $islandEntity): void
    {
        $mapEntity = $this->mapManager->getMapEntityByIsland($islandEntity);
//        $islandSettings = $islandEntity->getDataDecoded();
//        $map = array_fill(0, $islandSettings['rows'], array_fill(0, $islandSettings['cols'], TerrainType::PLAINS->getTitle()));
        foreach ($mapEntity as $entity) {
            $this->map[$entity[MapRepository::COL_X]][$entity[MapRepository::COL_Y]] = $entity[MapRepository::COL_TYPE];
            $this->mapTiles[$entity[MapRepository::COL_X]][$entity[MapRepository::COL_Y]] = $this->mapManager->build($entity->toArray());
        }
        bdump($this->map[6][16]);
        bdump($this->map[6][17]);
//        $this->map = $map;
    }

    public function getSeaLimit(): int
    {
        return (int) ceil(min($this->rows, $this->cols) * 0.1);
    }

    public function generateMap(string $name, string $code): void
    {
        $islandEntity = $this->islandManager->create($name, $code, $this->seed, Json::encode([
            'forrestProbability'        => $this->forrestProbability
            , 'mountainProbability'     => $this->mountainProbability
            , 'lakeProbability'         => $this->lakeProbability
            , 'emptyProbability'        => $this->emptyProbability
            , 'seaProbability'          => $this->seaProbability
            , 'emptyVillages'           => $this->emptyVillages
            , 'rows'                    => $this->rows
            , 'cols'                    => $this->cols
            , 'imageWidth'              => $this->imageWidth
            , 'imageHeight'             => $this->imageHeight
        ]));
        $islandEntity->save();
        mt_srand(crc32($this->seed));

        // vygenerování mapy
        $map = array_fill(0, $this->rows, array_fill(0, $this->cols, TerrainType::PLAINS->getTitle()));

        // vygenerování moře
        $seaLimit = $this->getSeaLimit();
        for ($i = 0; $i < $this->rows; $i++) {
            $map[$i][0] = TerrainType::SEA->getTitle();
            $map[$i][$this->cols - 1] = TerrainType::SEA->getTitle();
        }
        for ($j = 0; $j < $this->cols; $j++) {
            $map[0][$j] = TerrainType::SEA->getTitle();
            $map[$this->rows - 1][$j] = TerrainType::SEA->getTitle();
        }

        for ($layer = 1; $layer < $seaLimit; $layer++) {
            $probability = 1 - ($layer / $seaLimit) * $this->seaProbability;
            for ($i = $layer; $i < $this->rows - $layer; $i++) {
                for ($j = $layer; $j < $this->cols - $layer; $j++) {
                    if (
                        ($i === $layer || $i === $this->rows - $layer - 1 || $j === $layer || $j === $this->cols - $layer - 1) ||
                        (isset($map[$i][$j]) && $map[$i][$j] === TerrainType::SEA->getTitle())
                    ) {
                        if (mt_rand(0, 100) / 100.0 < $probability) {
                            $map[$i][$j] = TerrainType::SEA->getTitle(); // Moře
                        }
                    }
                }
            }
        }

        // entity mapy
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                if ($map[$i][$j] === TerrainType::PLAINS->getTitle()) {
                    if (mt_rand(0, 100) / 100.0 < $this->emptyProbability) {
                        $map[$i][$j] = TerrainType::PLAINS->getTitle();
                    } else {
                        $char = TerrainType::getDefaultTypes()[array_rand(TerrainType::getDefaultTypes())]->getTitle();
                        $map[$i][$j] = $char;

                        if ($char === TerrainType::FORREST->getTitle()) {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, TerrainType::FORREST->getTitle(), $this->forrestProbability);
                        } elseif ($char === TerrainType::MOUNTAINS->getTitle()) {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, TerrainType::MOUNTAINS->getTitle(), $this->mountainProbability);
                        } elseif ($char === TerrainType::LAKE->getTitle()) {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, TerrainType::LAKE->getTitle(), $this->lakeProbability);
                        }
                    }
                }
            }
        }

//        // safe spot - hráč
//        $safeCell = $this->findSafeEmptyCell($map, $this->rows, $this->cols, $seaLimit);
//        if ($safeCell !== null) {
//            $map[$safeCell[0]][$safeCell[1]] = 'P';
//        }
//
//        // safe spot - vesnice
//        for($i = 0; $i < $this->emptyVillages;$i++) {
//            $safeCell = $this->findSafeEmptyCell($map, $this->rows, $this->cols, $seaLimit);
//            if ($safeCell !== null) {
//                $map[$safeCell[0]][$safeCell[1]] = 'V';
//            }
//        }

        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                $this->mapManager->create($islandEntity, $map[$i][$j], $i, $j)->save();
            }
        }

        $this->map = $map;
    }

    private function isSafe(int $i, int $j, float $seaLimit): bool
    {
        return $i >= $seaLimit && $i < $this->rows - $seaLimit && $j >= $seaLimit && $j < $this->cols - $seaLimit;
    }

    public function findSafeEmptyCell(): ?array
    {
        $seaLimit = $this->getSeaLimit();
        $emptyCells = [];
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                if ($this->map[$i][$j] === TerrainType::PLAINS->getTitle() && $this->isSafe($i, $j, $seaLimit)) {
                    $emptyCells[] = [$i, $j];
                }
            }
        }

        if (count($emptyCells) > 0) {
            return $emptyCells[mt_rand(0, count($emptyCells) - 1)];
        }

        return null;
    }

    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @return MapEntity[][][]
     */
    public function getMapTiles(): array
    {
        return $this->mapTiles;
    }

    public function getCfg(): array
    {
        return [
            $this->imageWidth, $this->imageHeight,
            $this->cols, $this->rows
        ];
    }

    private function placeObject(array &$map, int $rows, int $cols, int $i, int $j, string $object, float $probability): void
    {
        foreach ($this->directions as $dir) {
            $ni = $i + $dir[0];
            $nj = $j + $dir[1];
            if ($ni >= 0 && $ni < $rows && $nj >= 0 && $nj < $cols && $map[$ni][$nj] === TerrainType::PLAINS->getTitle()) {
                if (rand(0, 100) / 100.0 < $probability) {
                    $map[$ni][$nj] = $object;
                }
            }
        }
    }

    private function drawObjectImage(GdImage $image, string $imagePath, int $x, int $y, int|float $width, int|float $height): void
    {
        $imagePath = __DIR__ . '/../../www/' . $imagePath;
        $objectImage = imagecreatefrompng($imagePath);
        imagecopyresampled($image, $objectImage, $x, $y, 0, 0, $width, $height, imagesx($objectImage), imagesy($objectImage));
        imagedestroy($objectImage);
    }

    public function calculate2dDistance(int $x1, int $y1, int $x2, int $y2): int
    {
        return (int) ceil(sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2))) + 1;
    }

    public function calculate3dDistance(int $x1, int $y1, int $x2, int $y2): ?int
    {
        $path = $this->calculatePath($x1, $y1, $x2, $y2);
        if ($path === null) {
            return null;
        }

        return count($path);
    }

    public function calculatePath(int $x1, int $y1, int $x2, int $y2): ?array
    {
        $openList = [];
        $closedList = [];
        $startNode = new CalculationNode($x1, $y1);
        $endNode = new CalculationNode($x2, $y2);
        array_push($openList, $startNode);

        while (!empty($openList)) {
            usort($openList, function($a, $b) {
                return $a->totalCost() <=> $b->totalCost();
            });

            /** @var CalculationNode $currentNode */
            $currentNode = array_shift($openList);
            $closedList[$currentNode->getX()][$currentNode->getY()] = true;

            // Dobro došli
            if ($currentNode->getX() === $endNode->getX() && $currentNode->getY() === $endNode->getY()) {
                $path = [];
                while ($currentNode != null) {
                    array_unshift($path, [$currentNode->getX(), $currentNode->getY()]);
                    $currentNode = $currentNode->getParent();
                }

                return $path;
            }

            foreach ($this->directions as $direction) {
                $newX = $currentNode->getX() + $direction[0];
                $newY = $currentNode->getY() + $direction[1];

                if (isset($this->map[$newX][$newY]) && !isset($closedList[$newX][$newY])) {
                    $char = $this->map[$newX][$newY];

                    if ($char === TerrainType::SEA->getTitle()) {
                        continue; // Neumíš plavat v moři (jezera zvládneš)
                    }

                    $newCost = $currentNode->getCost() + (TerrainType::getEnumByTitle($char)->getTravelCosts());
                    $heuristic = abs($newX - $x2) + abs($newY - $y2);
                    $newNode = new CalculationNode($newX, $newY, $newCost, $heuristic, $currentNode);
                    array_push($openList, $newNode);
                }
            }
        }

        return null; // Neexistuje cesta
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function setRows(int $rows): MapService
    {
        $this->rows = $rows;
        return $this;
    }

    public function getCols(): int
    {
        return $this->cols;
    }

    public function setCols(int $cols): MapService
    {
        $this->cols = $cols;
        return $this;
    }

    public function getForrestProbability(): float
    {
        return $this->forrestProbability;
    }

    public function setForrestProbability(float $forrestProbability): MapService
    {
        $this->forrestProbability = $forrestProbability;
        return $this;
    }

    public function getMountainProbability(): float
    {
        return $this->mountainProbability;
    }

    public function setMountainProbability(float $mountainProbability): MapService
    {
        $this->mountainProbability = $mountainProbability;
        return $this;
    }

    public function getLakeProbability(): float
    {
        return $this->lakeProbability;
    }

    public function setLakeProbability(float $lakeProbability): MapService
    {
        $this->lakeProbability = $lakeProbability;
        return $this;
    }

    public function getEmptyProbability(): float
    {
        return $this->emptyProbability;
    }

    public function setEmptyProbability(float $emptyProbability): MapService
    {
        $this->emptyProbability = $emptyProbability;
        return $this;
    }

    public function getSeaProbability(): float
    {
        return $this->seaProbability;
    }

    public function setSeaProbability(float $seaProbability): MapService
    {
        $this->seaProbability = $seaProbability;
        return $this;
    }

    public function getEmptyVillages(): int
    {
        return $this->emptyVillages;
    }

    public function setEmptyVillages(int $emptyVillages): MapService
    {
        $this->emptyVillages = $emptyVillages;
        return $this;
    }
}
