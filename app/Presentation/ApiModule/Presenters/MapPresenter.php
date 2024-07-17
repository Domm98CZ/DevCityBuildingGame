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
    private int $imageWidth = 750;
    private int $imageHeight = 750;
    private int $rows = 25;
    private int $cols = 25;
    private int $fontSize = 5;
    private array $mapObjects = [
        'L' // les
        , 'H' // hora
    ];

    private float $forestProbability = 0.6;
    private float $mountainProbability = 0.4;
    private float $emptyProbability = 0.95;
    private float $seaProbability = 0.5;

    private string $seed = 'dommDevGame';

    private array $map;

    public function actionData(): void
    {
        $this->generateMap();
        $this->getHttpResponse()->setCode(200);
        $this->sendResponse(new TextResponse(Json::encode($this->getMap(), true)));
    }

    public function actionImage(): void
    {
        try {
            $bgColorRgb = ImageColor::rgb(0, 0, 0);
            $fnColorRgb = ImageColor::rgb(255, 255, 255);
            $txColorRgb = ImageColor::rgb(255, 0, 0);

            $image = Image::fromBlank($this->imageWidth, $this->imageHeight, $bgColorRgb);
            $im = $image->getImageResource();

            $bColor = imagecolorallocate($im, $bgColorRgb->red, $bgColorRgb->green, $bgColorRgb->blue);
            $lnColor = imagecolorallocate($im, $fnColorRgb->red, $fnColorRgb->green, $fnColorRgb->blue);
            $txColor = imagecolorallocate($im, $txColorRgb->red, $txColorRgb->green, $txColorRgb->blue);

            $rowHeight = (int) ceil($this->imageHeight / $this->rows);
            $colWidth = (int) ceil($this->imageWidth / $this->cols);

            for ($i = 1; $i < $this->cols; $i++) {
                imageline($im, $i * $colWidth, 0, $i * $colWidth, $this->imageHeight, $lnColor);
            }

            for ($i = 1; $i < $this->rows; $i++) {
                imageline($im, 0, $i * $rowHeight, $this->imageWidth, $i * $rowHeight, $lnColor);
            }

            $this->generateMap();

            // doplnění písmen do mapy
            for ($i = 0; $i < $this->rows; $i++) {
                for ($j = 0; $j < $this->cols; $j++) {
                    if ($this->map[$i][$j] != '') {
                        $char = $this->map[$i][$j];
                        $x = (int) ceil($j * $colWidth + ($colWidth / 2) - (imagefontwidth($this->fontSize) / 2));
                        $y = (int) ceil($i * $rowHeight + ($rowHeight / 2) - (imagefontheight($this->fontSize) / 2));
                        imagestring($im, $this->fontSize, $x, $y, $char, $txColor);
                    }
                }
            }

            $image->send();
        } catch (Throwable $exception) {
            bdump($exception);
            Debugger::log($exception);
            $this->getHttpResponse()->setCode(404);
            $this->sendResponse(new TextResponse('not found'));
        }
        die;
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

        $this->map = $map;
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
