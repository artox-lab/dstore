<?php
/**
 * Reference: one doc linked to other documents
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\References;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Interfaces\ReferenceInterface;
use ArtoxLab\DStore\Redis\References\Builders\ReferenceDto;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\Indexes\StateBuilder;
use ArtoxLab\DStore\Redis\KeysResolver;
use ArtoxLab\DStore\Redis\References\Builders\ListBuilder;
use ArtoxLab\DStore\Serializers\JsonSerializer;
use Predis\ClientInterface;

abstract class HashListReference implements ReferenceInterface
{
    /**
     * State builder
     *
     * @var StateBuilder
     */
    protected $state;

    /**
     * Reference builder
     *
     * @var ListBuilder
     */
    protected $reference;

    /**
     * HashReference constructor.
     *
     * @param ClientInterface $redis          Redis
     * @param KeysResolver    $keys           Registry of keys
     * @param JsonSerializer  $jsonSerializer JsonSerializer
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys, JsonSerializer $jsonSerializer)
    {
        $this->state     = new StateBuilder();
        $this->reference = new ListBuilder($redis, $keys, $jsonSerializer);
    }

    /**
     * Indexing of document
     *
     * @param DocumentInterface $doc DocumentInterface
     *
     * @return void
     */
    public function persist(DocumentInterface $doc): void
    {
        $dto          = new ReferenceDto();
        $dto->name    = $this->getName();
        $dto->docType = $doc->getDocType();
        $dto->docId   = $doc->getDocId();

        $this->reference->build($dto, $this->getState($doc));
    }

    /**
     * Flush index of document
     *
     * @param DocumentInterface $doc DocumentInterface
     *
     * @return void
     */
    public function flush(DocumentInterface $doc) : void
    {
        $dto          = new ReferenceDto();
        $dto->name    = $this->getName();
        $dto->docType = $doc->getDocType();
        $dto->docId   = $doc->getDocId();

        $this->reference->build($dto, new State([], [], true));
    }

}
