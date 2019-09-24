<?php
/**
 * One to one index: one doc related with another one doc
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes;

use ArtoxLab\Domain\RelatedCollection;
use ArtoxLab\Domain\RelatedItem;
use DStore\Interfaces\DocumentInterface;
use DStore\Interfaces\IndexInterface;
use DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;
use Predis\Transaction\MultiExec;

abstract class ListIndex implements IndexInterface
{
    /**
     * Redis client
     *
     * @var Client
     */
    protected $redis;

    /**
     * Keys resolver
     *
     * @var KeysResolver
     */
    protected $keys;

    /**
     * ListIndex constructor.
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
     * Indexing of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function index(DocumentInterface $doc): void
    {
        $new = $this->getNewState();

        if ($new instanceof RelatedCollection) {
            $this->handleRelatedCollection($doc, $new);
            return;
        }

        if ($new instanceof RelatedItem) {
            $this->handleRelatedItem($doc, $new);
            return;
        }

        $name     = $this->getName();
        $sysKey   = $this->keys->makeIndexSysKey($doc->getDocType());
        $sysField = $this->getDocId() . ':' . $name;
        $this->redis->watch($this->keys->makeWatchingOnDocIndexKey($doc->getDocType(), $doc->getId(), $name));
        $actual      = (array) json_decode($this->redis->hget($sysKey, $sysField), true);
        $transaction = $this->beginTransaction($doc->getDocType(), $doc->getId());

        if (empty($actual) === false) {
            foreach ($actual as $value) {
                $transaction->srem($this->keys->makeIndexKey($doc->getDocType(), $name, $value), $doc->getId());
            }
        }

        if (empty($new) === false) {
            foreach ((array) $new as $value) {
                $transaction->sadd($this->keys->makeIndexKey($doc->getDocType(), $name, $value), [$doc->getId()]);
            }

            $transaction->hset($sysKey, $sysField, json_encode($new));
        } else {
            $transaction->hdel($sysKey, [$sysField]);
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->index($doc);
        }
    }

    /**
     * Begin transaction
     *
     * @param string $docType Document type
     * @param string $id      ID of document
     *
     * @return MultiExec
     */
    protected function beginTransaction(string $docType, string $id) : MultiExec
    {
        $transaction = $this->redis->transaction();
        $transaction->setex($this->keys->makeWatchingOnDocIndexKey($docType, $id, $this->getName()), 1, "");

        return $transaction;
    }

    /**
     * Handle related collection
     *
     * @param DocumentInterface $doc        Document
     * @param RelatedCollection $collection Related collection
     *
     * @return void
     */
    protected function handleRelatedCollection(DocumentInterface $doc, RelatedCollection $collection) : void
    {
        $name     = $this->getName();
        $sysKey   = $this->keys->makeIndexSysKey($doc->getDocType());
        $sysField = $this->getDocId() . ':' . $name;
        $this->redis->watch($this->keys->makeWatchingOnDocIndexKey($doc->getDocType(), $doc->getId(), $name));
        $actual      = json_decode($this->redis->hget($sysKey, $sysField), true);
        $transaction = $this->beginTransaction($doc->getDocType(), $doc->getId());

        if ($collection->isFlushed() === true) {
            foreach ($actual as $value) {
                $transaction->srem($this->keys->makeIndexKey($doc->getDocType(), $name, $value), $doc->getId());
            }

            $transaction->hdel($sysKey, [$sysField]);

            $actual = [];
        }

        if (empty($actual) === false && empty($deleted = $collection->deleted()) === false) {
            $deleted = array_map([$this, 'getStateValue'], $deleted);
            $actual  = array_diff($actual, $deleted);

            foreach ($deleted as $value) {
                $transaction->srem($this->keys->makeIndexKey($doc->getDocType(), $name, $value), $doc->getId());
            }

            if (empty($actual) === false) {
                $transaction->hset($sysKey, $sysField, json_encode($actual));
            } else {
                $transaction->hdel($sysKey, [$sysField]);
            }
        }

        if (empty($added = array_diff($collection->added(), $actual)) === true) {
            $added = array_map([$this, 'getStateValue'], $added);

            foreach ($added as $value) {
                $transaction->sadd(
                    $this->keys->makeIndexKey($doc->getDocType(), $name, $value),
                    [$doc->getId()]
                );
            }

            $transaction->hset($sysKey, $sysField, json_encode(array_merge($actual, $added)));
        }

        try {
            $transaction->execute();
            $collection->reset();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->handleRelatedCollection($doc, $collection);
        }
    }

    /**
     * Handle related item
     *
     * @param DocumentInterface $doc  Document
     * @param RelatedItem       $item Related item
     *
     * @return void
     */
    protected function handleRelatedItem(DocumentInterface $doc, RelatedItem $item) : void
    {
        $name     = $this->getName();
        $sysKey   = $this->keys->makeIndexSysKey($doc->getDocType());
        $sysField = $this->getDocId() . ':' . $name;
        $this->redis->watch($this->keys->makeWatchingOnDocIndexKey($doc->getDocType(), $doc->getId(), $name));
        $actual      = (array) json_decode($this->redis->hget($sysKey, $sysField), true);
        $transaction = $this->beginTransaction($doc->getDocType(), $doc->getId());

        foreach ($actual as $value) {
            $transaction->srem($this->keys->makeIndexKey($doc->getDocType(), $name, $value), $doc->getId());
        }

        $transaction->sadd(
            $this->keys->makeIndexKey($doc->getDocType(), $name, $this->getStateValue($item)),
            [$doc->getId()]
        );
        $transaction->hset($sysKey, $sysField, json_encode($item));

        try {
            $transaction->execute();
            $item->reset();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->handleRelatedItem($doc, $item);
        }
    }

    /**
     * Fetching value from state of changes like RelatedItem or RelatedCollection
     *
     * @param object|RelatedItem|RelatedCollection $item State of changes
     *
     * @return string
     */
    protected function getStateValue($item) : string
    {
        throw new \RuntimeException("Index %s doesn't have method getStateValue.", get_class($this));
    }

}
