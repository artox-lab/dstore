<?php
/**
 * Sorted index: one doc linked to other documents
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Interfaces\IndexInterface;
use ArtoxLab\DStore\Redis\Indexes\Builders\ListBuilder;
use ArtoxLab\DStore\Redis\Indexes\Builders\ListDto;
use ArtoxLab\DStore\Redis\Indexes\Builders\SortedListBuilder;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\ClientInterface;

abstract class SortedListIndex implements IndexInterface
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
     * ListIndex constructor.
     *
     * @param ClientInterface $redis Redis
     * @param KeysResolver    $keys  Registry of keys
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys)
    {
        $this->state = new StateBuilder();
        $this->index = new SortedListBuilder($redis, $keys);
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
        $dto          = new ListDto();
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
        $dto          = new ListDto();
        $dto->name    = $this->getName();
        $dto->docType = $doc->getDocType();
        $dto->docId   = $doc->getDocId();

        $this->index->build($dto, new State([], [], true));
    }

}
