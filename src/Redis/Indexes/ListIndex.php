<?php
/**
 * One to one index: one doc related with another one doc
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes;

use ArtoxLab\Domain\RelatedCollection;
use DStore\Interfaces\DocumentInterface;
use DStore\Interfaces\IndexInterface;
use DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
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
     * @param KeysResolver $keys Keys
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
        $name     = $this->getName();
        $sysKey   = $this->keys->makeIndexSysKey($doc->getDocType());
        $sysField = $this->getDocId() . ':' . $name;

        $old   = json_decode($this->redis->hget($sysKey, $sysField), true);
        $value = $this->getValue();

        if ($value instanceof RelatedCollection) {
        }

        if (empty($old) === false) {
            $this->redis->srem($this->keys->makeIndexKey($doc->getDocType(), $name, $old), $doc->getId());
        }

        if (empty($new) === false) {
            $this->redis->sadd($this->keys->makeIndexKey($doc->getDocType(), $name, $new), [$doc->getId()]);
        }

        $this->redis->hset($sysKey, $sysField, $new);
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

        $old = json_decode($this->redis->hget($sysKey, $sysField), true);

        $transaction = $this->beginTransaction($doc->getDocType(), $doc->getId());


    }

}
