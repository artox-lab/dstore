<?php
/**
 * Reference builder
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

namespace ArtoxLab\DStore\Redis\References\Builders;

use ArtoxLab\DStore\Interfaces\SerializerInterface;
use ArtoxLab\DStore\Redis\RedisConstants;
use ArtoxLab\DStore\Redis\References\Builders\ReferenceDto;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use ArtoxLab\DStore\Serializers\JsonSerializer;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Transaction\MultiExec;

abstract class ReferenceListBuilder
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
     * Serializer
     *
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * Add some items to list
     *
     * @param ReferenceDto $dto   Reference dto
     * @param array        $items Added items
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
     * @param ReferenceDto $dto   Reference dto
     * @param array        $items Deleted items
     *
     * @return void
     */
    abstract protected function delete(ReferenceDto $dto, array $items): void;

    /**
     * ListBuilder constructor.
     *
     * @param ClientInterface     $redis      Redis client
     * @param KeysResolver        $keys       Keys
     * @param SerializerInterface $serializer Serializer
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys, SerializerInterface $serializer)
    {
        $this->redis      = $redis;
        $this->keys       = $keys;
        $this->serializer = $serializer;
    }

    /**
     * Building index with new state
     *
     * @param ReferenceDto $dto   Reference dto
     * @param State        $state State
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
        $watchKey = $this->keys->makeWatchingOnDocReferenceKey($dto->docType, $dto->docId, $dto->name);
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
        $transaction->setex(
            $this->keys->makeWatchingOnDocReferenceKey($dto->docType, $dto->docId, $dto->name),
            RedisConstants::SETEX_TIMEOUT,
            ''
        );

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

        return $this->serializer->deserialize($data);
    }

}
