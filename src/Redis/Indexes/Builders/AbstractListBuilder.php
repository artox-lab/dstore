<?php
/**
 * Abstract List builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Transaction\MultiExec;

abstract class AbstractListBuilder
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
     * @param ListDto $dto   List dto
     * @param array   $items Added items
     *
     * @return void
     */
    abstract public function add(ListDto $dto, array $items) : void;

    /**
     * Flush actual state, be careful
     *
     * @param ListDto $dto List dto
     *
     * @return void
     */
    abstract protected function flush(ListDto $dto): void;

    /**
     * Delete some items from list
     *
     * @param ListDto $dto   List dto
     * @param array   $items Deleted items
     *
     * @return void
     */
    abstract protected function delete(ListDto $dto, array $items): void;

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
     * @param ListDto $dto   List dto
     * @param State   $state State
     *
     * @return void
     */
    public function build(ListDto $dto, State $state) : void
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
     * @param ListDto $dto List dto
     *
     * @return void
     */
    protected function watch(ListDto $dto) : void
    {
        $watchKey = $this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name);
        $this->redis->watch($watchKey);
    }

    /**
     * Begin transaction
     *
     * @param ListDto $dto List dto
     *
     * @return MultiExec
     */
    protected function beginTransaction(ListDto $dto) : MultiExec
    {
        $transaction = $this->redis->transaction();
        $transaction->setex($this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name), 1, '');

        return $transaction;
    }

    /**
     * Get system key
     *
     * @param ListDto $dto List dto
     *
     * @return string
     */
    protected function getSysKey(ListDto $dto) : string
    {

        return $this->keys->makeIndexSysKey($dto->docType);
    }

    /**
     * Getting system key of hash where we stored actual states of indexes
     *
     * @param ListDto $dto List dto
     *
     * @return string
     */
    protected function getSysHashKey(ListDto $dto) : string
    {
        return $this->keys->makeIndexSysHashKey($dto->docType, $dto->docId);
    }

    /**
     * Getting field of hash where we stored actual state
     *
     * @param ListDto $dto List dto
     *
     * @return string
     */
    protected function getSysField(ListDto $dto) : string
    {
        return $this->keys->makeSysField($dto->docId, $dto->docType);
    }

    /**
     * Getting actual state
     *
     * @param ListDto $dto List dto
     *
     * @return array
     */
    protected function getActualState(ListDto $dto) : array
    {
        $data = $this->redis->hget($this->getSysHashKey($dto), $this->getSysField($dto));

        if (empty($data) === true) {
            return [];
        }

        return (array) json_decode($data, true);
    }

}
