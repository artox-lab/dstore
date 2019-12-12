<?php
/**
 * Reference builder
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

namespace ArtoxLab\DStore\Redis\References\Builders;

use ArtoxLab\DStore\Redis\References\Builders\ReferenceDto;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Transaction\MultiExec;

abstract class ReferenceBuilder
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
     * @param ReferenceDto $dto Reference dto
     * @param array $items Added items
     *
     * @return void
     */
    abstract public function add(ReferenceDto $dto, array $items) : void;

    /**
     * Flush actual state, be careful
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return void
     */
    abstract protected function flush(ReferenceDto $dto): void;

    /**
     * Delete some items from list
     *
     * @param ReferenceDto $dto Reference dto
     * @param array $items Deleted items
     *
     * @return void
     */
    abstract protected function delete(ReferenceDto $dto, array $items): void;

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
     * @param ReferenceDto $dto Reference dto
     * @param State $state State
     *
     * @return void
     */
    public function build(ReferenceDto $dto, State $state) : void
    {
        if ($state->isShouldBeFlushed() === true) {
            $this->flush($dto);
        } else if ($state->hasDeletedItems() === true) {
            $this->delete($dto, $state->getDeletedItems());
        }
        
        $this->add($dto, $state->getAddedItems());
    }

    /**
     * Start watching for changes in reference
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return void
     */
    protected function watch(ReferenceDto $dto) : void
    {
        $watchKey = $this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name);
        $this->redis->watch($watchKey);
    }

    /**
     * Begin transaction
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return MultiExec
     */
    protected function beginTransaction(ReferenceDto $dto) : MultiExec
    {
        $transaction = $this->redis->transaction();
        $transaction->setex($this->keys->makeWatchingOnDocIndexKey($dto->docType, $dto->docId, $dto->name), 1, '');

        return $transaction;
    }

    /**
     * Get actual state from the set
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return array
     */
    protected function getActualState(ReferenceDto $dto): array
    {
        $data = $this->redis->hget($this->keys->makeReferenceKey($dto->docType), $dto->docId . ':' . $dto->name);

        if (empty($data) === true) {
            return [];
        }

        return (array) json_decode($data, true);
    }

    /**
     * Get actual state from hash
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return array
     */
    protected function getActualStateFromHash(ReferenceDto $dto) : array
    {
        $data = $this->redis->hget($this->keys->makeReferenceKey($dto->docType), $dto->docId . ':' . $dto->name);

        if (empty($data) === true) {
            return [];
        }

        return (array) json_decode($data, true);
    }

}
