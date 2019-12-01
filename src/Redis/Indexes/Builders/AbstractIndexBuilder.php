<?php
/**
 * Abstract Index builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Transaction\MultiExec;

abstract class AbstractIndexBuilder
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
     * Add some items to list
     *
     * @param IndexDto $dto   Index dto
     * @param array    $items Added items
     *
     * @return void
     */
    abstract public function add(IndexDto $dto, array $items) : void;

    /**
     * Flush actual state, be careful
     *
     * @param IndexDto $dto Index dto
     *
     * @return void
     */
    abstract protected function flush(IndexDto $dto): void;

    /**
     * Delete some items from list
     *
     * @param IndexDto $dto   Index dto
     * @param array    $items Deleted items
     *
     * @return void
     */
    abstract protected function delete(IndexDto $dto, array $items): void;

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
        } else if ($state->hasDeletedItems() === true) {
            $this->delete($dto, $state->getDeletedItems());
        }

        $this->add($dto, $state->getAddedItems());
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
        $transaction->setex($this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name), 1, '');

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
        return $this->keys->makeIndexSysKey($dto->docType, $dto->docId);
    }

    /**
     * Getting system key of hash where we stored actual states of indexes
     *
     * @param IndexDto $dto Index dto
     *
     * @return string
     */
    protected function getSysHashKey(IndexDto $dto) : string
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
    protected function getSysHashField(IndexDto $dto) : string
    {
        return $this->keys->makeSysField($dto->docId, $dto->docType);
    }

    /**
     * Get actual state from the set
     *
     * @param IndexDto $dto Index dto
     *
     * @return array
     */
    protected function getActualState(IndexDto $dto): array
    {
        return $this->redis->smembers($this->getSysKey($dto));
    }

    /**
     * Get actual state from hash
     *
     * @param IndexDto $dto Index dto
     *
     * @return array
     */
    protected function getActualStateFromHash(IndexDto $dto) : array
    {
        $data = $this->redis->hget($this->getSysHashKey($dto), $this->getSysHashField($dto));

        if (empty($data) === true) {
            return [];
        }

        return (array) json_decode($data, true);
    }

}
