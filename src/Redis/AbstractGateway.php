<?php
/**
 * Abstract gateway for store documents in Redis
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Interfaces\GatewayInterface;
use Predis\Client;
use Predis\ClientInterface;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;
use Predis\Transaction\MultiExec;

abstract class AbstractGateway implements GatewayInterface
{
    /**
     * Redis client
     *
     * @var Client
     */
    protected $redis;

    /**
     * Registry of keys
     *
     * @var KeysResolver
     */
    protected $keys;

    /**
     * AbstractGateway constructor.
     *
     * @param ClientInterface $redis Redis
     * @param KeysResolver    $keys  Registry of keys
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys)
    {
        $this->redis = $redis;
        $this->keys  = $keys;
    }

    /**
     * Persists document to store with their indexes and refs
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    protected function persist(DocumentInterface $doc) : void
    {
        $this->createOrUpdateDoc($doc);
        $this->persistIndexes();
        $this->persistRefs();
    }

    /**
     * Flush doc, their indexes and relations
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    protected function flush(DocumentInterface $doc) : void
    {
        $this->deleteIndexes();
        $this->deleteRefs();
        $this->deleteDoc($doc);
    }

    /**
     * Starting watch on changes of document, it avoids race conditions
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    protected function watchOnDocumentChanges(DocumentInterface $doc) : void
    {
        $this->redis->watch($this->keys->makeWatchingOnDocKey($doc->getDocType(), $doc->getId()));
    }

    /**
     * Begin transaction
     *
     * @param DocumentInterface $doc Document
     *
     * @return MultiExec
     */
    protected function beginTransaction(DocumentInterface $doc) : MultiExec
    {
        $transaction = $this->redis->transaction();
        $transaction->setex($this->keys->makeWatchingOnDocKey($doc->getDocType(), $doc->getId()), 1, "");

        return $transaction;
    }

    /**
     * Combine results with fields (ex. after hmget)
     *
     * @param array         $fields      Fields
     * @param array         $results     Array of results
     * @param callable|null $transformer Transform of one result
     *
     * @return array
     */
    protected function combineFieldsWithResults(array $fields, array $results, ?callable $transformer = null) : array
    {
        if (empty($transformer) === true) {
            $transformer = function (?string $result) : array {
                if (empty($result) === true) {
                    return [];
                }

                return json_decode($result, true);
            };
        }

        return array_combine(
            $fields,
            array_map(
                $transformer,
                $results
            )
        );
    }

    /**
     * Create or update document in hash table
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function createOrUpdateDoc(DocumentInterface $doc) : void
    {
        $this->watchOnDocumentChanges($doc);

        $actual = json_decode($this->redis->hget($this->keys->makeKey($doc->getDocType()), $doc->getId()), true);
        $new    = $doc->attributes();

        if ($actual === $new) {
            $this->redis->unwatch();
            return;
        }

        $transaction = $this->beginTransaction($doc);
        $transaction->hset($this->keys->makeKey($doc->getDocType()), $doc->getId(), json_encode($actual, $new));

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->createOrUpdateDoc($doc);
        }
    }

    /**
     * Removing document from hash table
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function deleteDoc(DocumentInterface $doc) : void
    {
        $this->watchOnDocumentChanges($doc);

        $transaction = $this->beginTransaction($doc);
        $transaction->hdel($this->keys->makeKey($doc->getDocType()), [$doc->getId()]);

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->deleteDoc($doc);
        }
    }

}
