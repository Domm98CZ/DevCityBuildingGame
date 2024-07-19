<?php declare(strict_types=1);
namespace App\Data\Player;

use App\Data\Account\AccountEntity;
use App\Data\Account\AccountManager;
use App\Data\BasicManager;
use App\Data\Island\IslandEntity;
use App\Data\Island\IslandManager;

final class PlayerManager extends BasicManager
{
    public function __construct(
        PlayerRepository $repository
        , private readonly AccountManager $accountManager
        , private readonly IslandManager $islandManager
    ) {
        parent::__construct($repository);
    }

    public function build(array $data): PlayerEntity
    {
        $entity = new PlayerEntity(
            $this
            , $this->accountManager->get($data[PlayerRepository::COL_ID_ACCOUNT])
            , $this->islandManager->get($data[PlayerRepository::COL_ID_ISLAND])
            , $data[PlayerRepository::COL_ID] ?? null
        );

        if (!empty($data[PlayerRepository::COL_ID])) {
            $this->entity[$data[PlayerRepository::COL_ID]] = $entity;
            $this->original[$data[PlayerRepository::COL_ID]] = $entity->copy();
        }
        return $entity;
    }

    public function isAccountJoinedIsland(AccountEntity $accountEntity, IslandEntity $islandEntity): ?PlayerEntity
    {
        $data = $this->repository->isAccountJoinedIsland($accountEntity, $islandEntity)->fetch();
        if ($data !== null) {
            return $this->get($data[PlayerRepository::COL_ID]);
        }
        return null;
    }

    /**
     * @return PlayerEntity[]
     */
    public function getJoinedPlayers(IslandEntity $islandEntity): array
    {
        $data = $this->repository->getPlayersIslandSelection($islandEntity);
        $list = [];
        foreach ($data as $item) {
            $list[$item[PlayerRepository::COL_ID]] = $this->build($item->toArray());
        }
        return $list;
    }

    public function create(AccountEntity $accountEntity, IslandEntity $islandEntity): PlayerEntity
    {
        return new PlayerEntity($this, $accountEntity, $islandEntity, null);
    }
}
