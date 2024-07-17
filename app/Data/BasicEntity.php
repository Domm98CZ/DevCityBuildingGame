<?php declare(strict_types=1);
namespace App\Data;

abstract class BasicEntity
{
    public function __construct(
        protected readonly BasicManager $manager, protected ?int $id
    ) {

    }

    public abstract function copy(): BasicEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): BasicEntity
    {
        $this->id = $id;
        return $this;
    }

    public function getManager(): BasicManager
    {
        return $this->manager;
    }

    public function save(): void
    {
        if ($this->getId() !== null) {
            $this->getManager()->update($this);
        } else {
            $this->setId($this->getManager()->insert($this));
        }
    }


    public function __toArray(): array
    {
        $name = get_class($this);
        $name = str_replace('\\', "\\\\", $name);
        $raw = (array) $this;

        $attributes = [];
        foreach ($raw as $attr => $val) {
            if ($val instanceof BasicManager) {
                continue;
            }

            if ($val instanceof BasicEntity) {
                bdump($val);
                if ($val->getId() !== null) {
                    $val = $val->getId();
                } else {
                    continue;
                }
            }

            $attributes[trim(preg_replace('('.$name.'|\*|)', '', $attr))] = $val;
        }
        return $attributes;
    }

}
