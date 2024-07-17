<?php declare(strict_types=1);
namespace App\Presentation\ApiModule\Presenters;

use Nette\Application\Responses\TextResponse;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\Json;
use Throwable;
use Tracy\Debugger;

final class MapPresenter extends BasePresenter
{
    private int $imageWidth = 800;
    private int $imageHeight = 800;
    private int $rows = 20;
    private int $cols = 20;
    private array $mapObjects = [
        'L'
        , 'H'
    ];
    private array $mapObjectColors = [
        ''      => [221, 232, 172] // prázdno
        , 'L'   => [34, 139, 34]   // les
        , 'H'   => [105, 105, 105] // hora
        , 'S'   => [0, 105, 148]   // moře
        , 'P'   => [255, 0, 0]     // hráč
        , 'V'   => [0, 0, 255]     // vesnice
    ];
    private array $imagePaths  = [
        ''      => '/assets/images/map/plains.png' // prázdno
//        , 'L'   => '/assets/images/map/forest.png' // les
//        , 'H'   => '' // hora
        , 'S'   => '/assets/images/map/sea.png' // moře
//        , 'P'   => '' // hráč
//        , 'V'   => '' // vesnice
    ];

    private float $forestProbability = 0.6;
    private float $mountainProbability = 0.4;
    private float $emptyProbability = 0.95;
    private float $seaProbability = 0.5;
    private int $emptyVillages = 50;

    private string $seed = 'dommDevGame';

    private array $map;

    public function actionData(?string $id = null): void
    {
        if ($id !== null) {
            $this->setSeed($id);
        }

        $this->generateMap();
        $this->getHttpResponse()->setCode(200);
        $this->sendResponse(new TextResponse(Json::encode($this->getMap(), true)));
    }

    public function actionImage(?string $id = null): void
    {
        try {
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

            if ($id !== null) {
                $this->setSeed($id);
            }

            // generator mapy
            $this->generateMap();



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
//
//            // doplnění grid čar do mapy
//            for ($i = 1; $i < $this->cols; $i++) {
//                imageline($im, $i * $colWidth, 0, $i * $colWidth, $this->imageHeight, $lnColor);
//            }
//
//            for ($i = 1; $i < $this->rows; $i++) {
//                imageline($im, 0, $i * $rowHeight, $this->imageWidth, $i * $rowHeight, $lnColor);
//            }

            $image->send();
        } catch (Throwable $exception) {
            bdump($exception);
            Debugger::log($exception);
            $this->getHttpResponse()->setCode(404);
            $this->sendResponse(new TextResponse('not found'));
        }
        die;
    }

    private function drawObjectImage($image, $imagePath, $x, $y, $width, $height): void
    {
        $imagePath = __DIR__ . '/../../../../www/' . $imagePath;
        $objectImage = imagecreatefrompng($imagePath);
        imagecopyresampled($image, $objectImage, $x, $y, 0, 0, $width, $height, imagesx($objectImage), imagesy($objectImage));
        imagedestroy($objectImage);
    }

    public function setSeed(string $seed): void
    {
        $this->seed = $seed;
    }

    public function generateMap(): void
    {
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
                        ($i == $layer || $i == $this->rows - $layer - 1 || $j == $layer || $j == $this->cols - $layer - 1) ||
                        (isset($map[$i][$j]) && $map[$i][$j] == 'S')
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
                if ($map[$i][$j] == '') {
                    if (mt_rand(0, 100) / 100.0 < $this->emptyProbability) {
                        $map[$i][$j] = '';
                    } else {
                        $char = $this->mapObjects[array_rand($this->mapObjects)];
                        $map[$i][$j] = $char;
                        if ($char == 'L') {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, 'L', $this->forestProbability);
                        } elseif ($char == 'H') {
                            self::placeObject($map, $this->rows, $this->cols, $i, $j, 'H', $this->mountainProbability);
                        }
                    }
                }
            }
        }

        // safe spot - hráč
        $safeCell = $this->findSafeEmptyCell($map, $this->rows, $this->cols, $seaLimit);
        if ($safeCell !== null) {
            $map[$safeCell[0]][$safeCell[1]] = 'P';
        }

        // safe spot - vesnice
        for($i = 0; $i < $this->emptyVillages;$i++) {
            $safeCell = $this->findSafeEmptyCell($map, $this->rows, $this->cols, $seaLimit);
            if ($safeCell !== null) {
                $map[$safeCell[0]][$safeCell[1]] = 'V';
            }
        }


        $this->map = $map;
    }

    private function isSafe($map, $rows, $cols, $i, $j, $seaLimit): bool
    {
        return $i >= $seaLimit && $i < $rows - $seaLimit && $j >= $seaLimit && $j < $cols - $seaLimit;
    }

    public function findSafeEmptyCell($map, $rows, $cols, $seaLimit): ?array
    {
        $emptyCells = [];
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                if ($map[$i][$j] === '' && $this->isSafe($map, $rows, $cols, $i, $j, $seaLimit)) {
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

    private function placeObject(&$map, $rows, $cols, $i, $j, $object, $probability): void
    {
        $directions = [
            [-1, 0], [1, 0], [0, -1], [0, 1], // N, S, W, E
            [-1, -1], [-1, 1], [1, -1], [1, 1] // NW, NE, SW, SE
        ];
        foreach ($directions as $dir) {
            $ni = $i + $dir[0];
            $nj = $j + $dir[1];
            if ($ni >= 0 && $ni < $this->rows && $nj >= 0 && $nj < $this->cols && $map[$ni][$nj] == '') {
                if (rand(0, 100) / 100.0 < $probability) {
                    $map[$ni][$nj] = $object;
                }
            }
        }
    }
}
