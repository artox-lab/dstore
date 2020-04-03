<?php
/**
 * Interface of indexes
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;

use ArtoxLab\DStore\Redis\Indexes\State;

interface IndexInterface
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName() : string;

    /**
     * State of value for filtering documents
     *
     * @param DocumentInterface $doc Document
     *
     * @return string|array|State
     */
    public function getState(DocumentInterface $doc);

    /**
     * Indexing of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function index(DocumentInterface $doc) : void;

    /**
     * Flush index of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function flush(DocumentInterface $doc) : void;

    /**
     * Indicates if the index should index.
     *
     * @param DocumentInterface $doc Document
     *
     * @return bool
     */
    public function shouldIndex(DocumentInterface $doc): bool;

}
