<?php
/**
 * One to one index: one doc related with another one doc
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes;

use DStore\Interfaces\DocumentInterface;
use DStore\Interfaces\IndexInterface;
use DStore\Redis\Indexes\Builders\ListBuilder;
use DStore\Redis\Indexes\Builders\ListDto;

abstract class ListIndex implements IndexInterface
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
     * @param StateBuilder $state State builder
     * @param ListBuilder  $index List index builder
     */
    public function __construct(StateBuilder $state, ListBuilder $index)
    {
        $this->state = $state;
        $this->index = $index;
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
        $dto->docId   = $doc->getId();

        $this->index->build($dto, $this->getNewState($doc));
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
        $dto->docId   = $doc->getId();

        $this->index->build($dto, new State([], [], true));
    }

}
