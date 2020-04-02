<?php
/**
 * Unique index: one doc linked to one document
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Interfaces\IndexInterface;
use ArtoxLab\DStore\Redis\Indexes\Builders\ListBuilder;
use ArtoxLab\DStore\Redis\Indexes\Builders\IndexDto;
use ArtoxLab\DStore\Redis\Indexes\Builders\DictionaryBuilder;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\ClientInterface;

abstract class DictionaryIndex implements IndexInterface
{
    /**
     * State builder
     *
     * @var StateBuilder
     */
    protected $state;

    /**
     * Index builder
     *
     * @var ListBuilder
     */
    protected $index;

    /**
     * DictionaryIndex constructor.
     *
     * @param ClientInterface $redis Redis
     * @param KeysResolver    $keys  Registry of keys
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys)
    {
        $this->state = new StateBuilder();
        $this->index = new DictionaryBuilder($redis, $keys);
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
        $dto          = new IndexDto();
        $dto->name    = $this->getName();
        $dto->docType = $doc->getDocType();
        $dto->docId   = $doc->getDocId();

        $this->index->build($dto, $this->getState($doc));
    }

    /**
     * Flush index of document
     *
     * @param DocumentInterface $doc Документ
     *
     * @return void
     */
    public function flush(DocumentInterface $doc) : void
    {
        $dto          = new IndexDto();
        $dto->name    = $this->getName();
        $dto->docType = $doc->getDocType();
        $dto->docId   = $doc->getDocId();

        $this->index->build($dto, new State([], [], true));
    }

    /**
     * Indicates if the index should index/flush.
     *
     * @param DocumentInterface $doc Document
     *
     * @return bool
     */
    public function shouldHandle(DocumentInterface $doc): bool
    {
        return true;
    }

}
