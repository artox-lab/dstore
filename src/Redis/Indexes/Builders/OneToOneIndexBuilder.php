<?php
/**
 * Abstract One to One Index builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use ArtoxLab\DStore\Redis\RedisConstants;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Transaction\MultiExec;

abstract class OneToOneIndexBuilder
{
    /**
     * Redis
     *
     * @var Client|ClientInterface
     */
    protected $redis;

    /**
     * Keys resolver
     *
     * @var KeysResolver
     */
    protected $keys;

    /**
     * Persist item
     *
     * @param IndexDto $dto  Index dto
     * @param string   $item Added item
     *
     * @return void
     */
    abstract public function persist(IndexDto $dto, string $item) : void;

    /**
     * Flush actual state, be careful
     *
     * @param IndexDto $dto Index dto
     *
     * @return void
     */
    abstract protected function flush(IndexDto $dto): void;

    /**
     * ListBuilder constructor.
     *
     * @param ClientInterface $redis Redis client
     * @param KeysResolver    $keys  Keys
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys)
    {
        $this->redis = $redis;
        $this->keys  = $keys;
    }

    /**
     * Building index with new state
     *
     * @param IndexDto $dto   Index dto
     * @param State    $state State
     *
     * @return void
     */
    public function build(IndexDto $dto, State $state) : void
    {
        if ($state->isShouldBeFlushed() === true) {
            $this->flush($dto);
        }

        $items = $state->getAddedItems();
        $item  = array_shift($items);

        $this->persist($dto, $item);
    }

    /**
     * Start watching for changes in index
     *
     * @param IndexDto $dto Index dto
     *
     * @return void
     */
    protected function watch(IndexDto $dto) : void
    {
        $watchKey = $this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name);
        $this->redis->watch($watchKey);
    }

    /**
     * Begin transaction
     *
     * @param IndexDto $dto Index dto
     *
     * @return MultiExec
     */
    protected function beginTransaction(IndexDto $dto) : MultiExec
    {
        $transaction = $this->redis->transaction();
        $transaction->setex(
            $this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name),
            RedisConstants::SETEX_TIMEOUT,
            ''
        );

        return $transaction;
    }

    /**
     * Get system key
     *
     * @param IndexDto $dto Index dto
     *
     * @return string
     */
    protected function getSysKey(IndexDto $dto) : string
    {
        return $this->keys->makeIndexSysHashKey($dto->docType);
    }

    /**
     * Getting field of hash where we stored actual state
     *
     * @param IndexDto $dto Index dto
     *
     * @return string
     */
    protected function getSysField(IndexDto $dto) : string
    {
        return $this->keys->makeSysField($dto->docId, $dto->name);
    }

    /**
     * Get actual state from the set
     *
     * @param IndexDto $dto Index dto
     *
     * @return string|null
     */
    protected function getActualState(IndexDto $dto): ?string
    {
        return $this->redis->hget($this->getSysKey($dto), $this->getSysField($dto));
    }

}
