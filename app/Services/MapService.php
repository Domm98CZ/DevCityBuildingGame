<?php declare(strict_types=1);

namespace App\Services;

use App\Data\CalculationNode;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;
use App\Data\Map\MapManager;
use App\Data\Map\MapRepository;
use GdImage;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\Json;
use Nette\Utils\Random;

final class MapService
{
    private int $imageWidth = 800;
    private int $imageHeight = 800;
    private int $rows = 20;
    private int $cols = 20;
    private array $mapObjects = [
        'L', 'H', 'J'
    ];
    private array $mapObjectColors = [
        ''      => [221, 232, 172] // prázdno
        , 'L'   => [34, 139, 34]   // les
        , 'J'   => [0, 0, 255]     // jezero
        , 'H'   => [105, 105, 105] // hora
        , 'S'   => [0, 105, 148]   // moře
        , 'P'   => [255, 0, 0]     // hráč
        , 'V'   => [255, 140, 0]   // vesnice
    ];
    private float $defaultTerrainCosts = 1.0;
    private array $terrainCosts = [
        'L' => 1.5,
        'H' => 2
    ];
    private array $directions = [
        [-1, 0], [1, 0], [0, -1], [0, 1], // N, S, W, E
        [-1, -1], [-1, 1], [1, -1], [1, 1] // NW, NE, SW, SE
    ];
    private array $imagePaths  = [
        ''      => '/assets/images/map/plains.png' // prázdno
//        , 'L'   => '/assets/images/map/forest.png' // les
//        , 'H'   => '' // hora
        , 'S'   => '/assets/images/map/sea.png' // moře
        , 'J'   => '/assets/images/map/lake.png' // jezero
//        , 'P'   => '' // hráč
//        , 'V'   => '' // vesnice
    ];
    private array $map;
    private float $forestProbability = 0.6;
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

    public function generateImage(?IslandEntity $islandEntity): Image
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

        // generator mapy
        if ($islandEntity instanceof IslandEntity) {
            $this->loadMap($islandEntity);
        } else {
            $this->generateMap();
        }

        // vykreslení písmen do mapy
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                $char = $this->map[$i][$j];

                if (isset($this->imagePaths[$char])) {
                    $this->drawObjectImage($im, $this->imagePaths[$char], $j * $colWidth,  $i * $rowHeight, $colWidth, $rowHeight);
                } else {
                    $bgColor = imagecolorallocate($im, $this->mapObjectColors[$char][0], $this->mapObjectColors[$char][1], $this->mapObjectColors[$char][2]);
                    imagefilledrectangle($im, $j * $colWidth, $i * $rowHeight, ($j + 1) * $colWidth, ($i + 1) * $rowHeight, $bgColor);
                }

                if ($char != '') {
                    $x = (int) ceil($j * $colWidth + ($colWidth / 2) - (imagefontwidth($fontSize) / 2));
                    $y = (int) ceil($i * $rowHeight + ($rowHeight / 2) - (imagefontheight($fontSize) / 2));
                    imagestring($im, $fontSize, $x, $y, $char, $txColor);
                }
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
        $islandSettings = Json::decode($islandEntity->getData(), true);

        $map = array_fill(0, $islandSettings['rows'], array_fill(0, $islandSettings['cols'], ''));

        foreach ($mapEntity as $entity) {
            $map[$entity[MapRepository::COL_X]][$entity[MapRepository::COL_Y]] = $entity[MapRepository::COL_TYPE];
        }

        $this->map = $map;
    }

    public function generateMap(string $name, string $code): void
    {
        $islandEntity = $this->islandManager->create($name, $code, $this->seed, Json::encode([
            'forestProbability'         => $this->forestProbability
            , 'mountainProbability'     => $this->mountainProbability
            , 'lakeProbability'         => $this->lakeProbability
            , 'emptyProbability'        => $this->emptyProbability
            , 'seaProbability'          => $this->seaProbability
            , 'emptyVillages'           => $this->emptyVillages
            , 'rows'                    => $this->rows
            , 'cols'                    => $this->cols
        ]));
        $islandEntity->save();

        mt_srand(crc32($this->seed));

        // vygenerování mapy
        $map = array_fill(0, $this->rows, array_fill(0, $this->cols, ''));

        // vygenerování moře
        $seaLimit = ceil(min($this->rows, $this->cols) * 0.1);
        for ($i = 0; $i < $this->rows; $i++) {
            $map[$i][0] = 'S';
            $map[$i][$this->cols - 1] = 'S';
        }
        for ($j = 0; $j < $this->cols; $j++) {
            $map[0][$j] = 'S';
            $map[$this->rows - 1][$j] = 'S';
        }

        for ($layer = 1; $layer < $seaLimit; $layer++) {
            $probability = 1 - ($layer / $seaLimit) * $this->seaProbability;
            for ($i = $layer; $i < $this->rows - $layer; $i++) {
                for ($j = $layer; $j < $this->cols - $layer; $j++) {
                    if (
                        ($i === $layer || $i === $this->rows - $layer - 1 || $j === $layer || $j === $this->cols - $layer - 1) ||
                        (isset($map[$i][$j]) && $map[$i][$j] === 'S')
                    ) {
                        if (mt_rand(0, 100) / 100.0 < $probability) {
                            $map[$i][$j] = 'S'; // Moře
                        }
                    }
                }
            }
        }

        // entity mapy
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                if ($map[$i][$j] === '') {
                    if (mt_rand(0, 100) / 100.0 < $this->emptyProbability) {
                        $map[$i][$j] = '';
                    } else {
                        $char = $this->mapObjects[array_rand($this->mapObjects)];
                        $map[$i][$j] = $char;
                        if ($char === 'L') {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, 'L', $this->forestProbability);
                        } elseif ($char === 'H') {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, 'H', $this->mountainProbability);
                        } elseif ($char === 'J') {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, 'H', $this->lakeProbability);
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
                if ($map[$i][$j] !== '') {
                    $this->mapManager->create($islandEntity, $map[$i][$j], $i, $j)->save();
                }
            }
        }

        $this->map = $map;
    }

    private function isSafe(int $rows, int $cols, int $i, int $j, float $seaLimit): bool
    {
        return $i >= $seaLimit && $i < $rows - $seaLimit && $j >= $seaLimit && $j < $cols - $seaLimit;
    }

    public function findSafeEmptyCell(array $map, int $rows, int $cols, float $seaLimit): ?array
    {
        $emptyCells = [];
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                if ($map[$i][$j] === '' && $this->isSafe($rows, $cols, $i, $j, $seaLimit)) {
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
            if ($ni >= 0 && $ni < $rows && $nj >= 0 && $nj < $cols && $map[$ni][$nj] === '') {
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
                    $terrainType = $this->map[$newX][$newY];

                    if ($terrainType === 'J' || $terrainType === 'S') {
                        continue; // Neumíš plavat
                    }

                    $newCost = $currentNode->getCost() + ($this->terrainCosts[$terrainType] ?? $this->defaultTerrainCosts);
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

    public function getDefaultTerrainCosts(): float
    {
        return $this->defaultTerrainCosts;
    }

    public function setDefaultTerrainCosts(float $defaultTerrainCosts): MapService
    {
        $this->defaultTerrainCosts = $defaultTerrainCosts;
        return $this;
    }

    public function getTerrainCosts(): array
    {
        return $this->terrainCosts;
    }

    public function setTerrainCosts(array $terrainCosts): MapService
    {
        $this->terrainCosts = $terrainCosts;
        return $this;
    }

    public function getForestProbability(): float
    {
        return $this->forestProbability;
    }

    public function setForestProbability(float $forestProbability): MapService
    {
        $this->forestProbability = $forestProbability;
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
