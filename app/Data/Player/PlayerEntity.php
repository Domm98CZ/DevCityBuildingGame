<?php declare(strict_types=1);
namespace App\Data\Player;

use App\Data\Account\AccountEntity;
use App\Data\BasicEntity;
use App\Data\Island\IslandEntity;

final class PlayerEntity extends BasicEntity
{
    public function __construct(
        PlayerManager $manager
        , private AccountEntity $accountEntity
        , private IslandEntity $islandEntity
        , ?int $id
    ) {
        parent::__construct($manager, $id);
    }

    public function getAccountEntity(): AccountEntity
    {
        return $this->accountEntity;
    }

    public function setAccountEntity(AccountEntity $accountEntity): PlayerEntity
    {
        $this->accountEntity = $accountEntity;
        return $this;
    }

    public function getIslandEntity(): IslandEntity
    {
        return $this->islandEntity;
    }

    public function setIslandEntity(IslandEntity $islandEntity): PlayerEntity
    {
        $this->islandEntity = $islandEntity;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->getAccountEntity()->getName() ?? $this->getAccountEntity()->getUsername();
    }

    public function copy(): PlayerEntity
    {
        return new PlayerEntity(
            $this->getManager()
            , $this->getAccountEntity()
            , $this->getIslandEntity()
            , $this->getId()
        );
    }
}
