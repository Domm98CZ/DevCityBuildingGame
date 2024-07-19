<?php declare(strict_types=1);
namespace App\Data\Island;

use App\Data\BasicEntity;
use App\Data\Map\MapManager;
use Nette\Utils\Json;

final class IslandEntity extends BasicEntity
{
    public function __construct(
        IslandManager $manager
        , ?int $id
        , private string $name
        , private string $code
        , private string $seed
        , private string $data
        , private bool $started
        , private bool $finished
    ) {
        parent::__construct($manager, $id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): IslandEntity
    {
        $this->name = $name;
        return $this;
    }

    public function getSeed(): string
    {
        return $this->seed;
    }

    public function setSeed(string $seed): IslandEntity
    {
        $this->seed = $seed;
        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getDataDecoded(): array
    {
        return Json::decode($this->getData(), true);
    }

    public function setData(string $data): IslandEntity
    {
        $this->data = $data;
        return $this;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function setStarted(bool $started): IslandEntity
    {
        $this->started = $started;
        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): IslandEntity
    {
        $this->finished = $finished;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): IslandEntity
    {
        $this->code = $code;
        return $this;
    }

    public function getTitle(): string
    {
        return sprintf('[%s] %s', $this->getCode(), $this->getName());
    }

    public function copy(): IslandEntity
    {
        return new IslandEntity(
            $this->getManager()
            , $this->getId()
            , $this->getName()
            , $this->getCode()
            , $this->getSeed()
            , $this->getData()
            , $this->isStarted()
            , $this->isFinished()
        );
    }
}
