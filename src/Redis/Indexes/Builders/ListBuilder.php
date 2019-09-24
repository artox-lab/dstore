<?php
/**
 * Index of list (set) builder
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes\Builders;

use DStore\Redis\Indexes\State;
use DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;
use Predis\Transaction\MultiExec;

class ListBuilder
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
        $transaction->setex($this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name), 1, "");

        return $transaction;
    }

    /**
     * Getting system key of hash where we stored actual states of indexes
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
     * Getting field of hash where we stored actual state
     *
     * @param ListDto $dto List dto
     *
     * @return string
     */
    protected function getSysField(ListDto $dto) : string
    {
        return ($dto->docId . ':' . $dto->name);
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
        $data = $this->redis->hget($this->getSysKey($dto), $this->getSysField($dto));

        if (empty($data) === true) {
            return [];
        }

        return (array) json_decode($data, true);
    }

    /**
     * Flush actual state, be careful
     *
     * @param ListDto $dto List dto
     *
     * @return void
     */
    protected function flush(ListDto $dto) : void
    {
        $this->watch($dto);

        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        foreach ($actual as $value) {
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $value), $dto->docId);
        }

        $transaction->hdel($this->getSysKey($dto), [$this->getSysField($dto)]);

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Deleting some items from list
     *
     * @param ListDto $dto   List dto
     * @param array   $items Deleted items
     *
     * @return void
     */
    protected function delete(ListDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $this->watch($dto);

        $actual = $this->getActualState($dto);

        if (empty($actual) === true) {
            return;
        }

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $item), $dto->docId);
        }

        $actual = array_diff($actual, $items);

        if (empty($actual) === false) {
            $transaction->hset($this->getSysKey($dto), $this->getSysField($dto), json_encode($actual));
        } else {
            $transaction->hdel($this->getSysKey($dto), [$this->getSysField($dto)]);
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->delete($dto, $items);
        }
    }

    /**
     * Adding some items to list
     *
     * @param ListDto $dto   List dto
     * @param array   $items Added items
     *
     * @return void
     */
    public function add(ListDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $this->watch($dto);

        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        $items = array_diff($items, $actual);

        foreach ($items as $item) {
            $transaction->sadd(
                $this->keys->makeIndexKey($dto->docType, $dto->name, $item),
                [$dto->docId]
            );
        }

        $transaction->hset(
            $this->getSysKey($dto),
            $this->getSysField($dto),
            json_encode(array_merge($actual, $items))
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
