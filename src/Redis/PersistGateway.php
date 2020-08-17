<?php
/**
 * Abstract gateway for store documents in Redis
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Interfaces\PersistGatewayInterface;
use ArtoxLab\DStore\Interfaces\IndexInterface;
use ArtoxLab\DStore\Interfaces\ReferenceInterface;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;
use Predis\Transaction\MultiExec;

abstract class PersistGateway extends AbstractGateway
{

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
        $this->persistIndexes($doc);
        $this->persistRefs($doc);
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
        $this->deleteIndexes($doc);
        $this->deleteRefs($doc);
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
        $this->redis->watch($this->keys->makeWatchingOnDocKey($doc->getDocType(), $doc->getDocId()));
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
        $transaction->setex(
            $this->keys->makeWatchingOnDocKey($doc->getDocType(), $doc->getDocId()),
            RedisConstants::SETEX_TIMEOUT,
            ""
        );

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

                return $this->serializer->deserialize($result);
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

        $existedData = (string) $this->redis->hget($this->keys->makeKey($doc->getDocType()), $doc->getDocId());
        $existedData = (array) $this->serializer->deserialize($existedData);
        $newData     = $doc->getDocAttributes();

        if (empty($existedData) === false && $existedData === $newData) {
            $this->redis->unwatch();
            return;
        }

        $actualData  = array_merge($existedData, $newData);
        $transaction = $this->beginTransaction($doc);
        $transaction->hset(
            $this->keys->makeKey($doc->getDocType()),
            $doc->getDocId(),
            $this->serializer->serialize($actualData)
        );

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
        $transaction->hdel($this->keys->makeKey($doc->getDocType()), [$doc->getDocId()]);

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->deleteDoc($doc);
        }
    }

    /**
     * Persisting indexes of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function persistIndexes(DocumentInterface $doc) : void
    {
        $classes = $doc->getDocIndexes();

        if (empty($classes) === true) {
            return;
        }

        foreach ($classes as $class) {
            $index = $this->makeIndexByClassName($class);

            if ($index->shouldIndex($doc) === true) {
                $index->index($doc);
            }
        }
    }

    /**
     * Flushing indexes of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function deleteIndexes(DocumentInterface $doc) : void
    {
        $classes = $doc->getDocIndexes();

        if (empty($classes) === true) {
            return;
        }

        foreach ($classes as $class) {
            $index = $this->makeIndexByClassName($class);

            $index->flush($doc);
        }
    }

    /**
     * Persisting references of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function persistRefs(DocumentInterface $doc) : void
    {
        $classes = $doc->getDocReferences();

        if (empty($classes) === true) {
            return;
        }

        foreach ($classes as $class) {
            $ref = $this->makeReferenceByClassName($class);

            if ($ref->shouldIndex($doc) === true) {
                $ref->persist($doc);
            }
        }
    }

    /**
     * Flushing references of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    private function deleteRefs(DocumentInterface $doc) : void
    {
        $classes = $doc->getDocReferences();

        if (empty($classes) === true) {
            return;
        }

        foreach ($classes as $class) {
            $ref = $this->makeReferenceByClassName($class);

            $ref->flush($doc);
        }
    }

    /**
     * Creating instance of index by class name
     *
     * @param string $class Index's class name
     *
     * @return IndexInterface
     */
    private function makeIndexByClassName(string $class) : IndexInterface
    {
        if (empty($class) === true) {
            throw new \RuntimeException("Name of index can't be a empty string");
        }

        if (class_exists($class) === false) {
            throw new \RuntimeException("Name of index can't be a empty string");
        }

        return new $class($this->redis, $this->keys);
    }

    /**
     * Creating instance of reference by class name
     *
     * @param string $class Refs's class name
     *
     * @return ReferenceInterface
     */
    private function makeReferenceByClassName(string $class) : ReferenceInterface
    {
        if (empty($class) === true) {
            throw new \RuntimeException("Name of reference can't be a empty string");
        }

        if (class_exists($class) === false) {
            throw new \RuntimeException("Class of reference does not exist");
        }

        return new $class($this->redis, $this->keys, $this->serializer);
    }

}
