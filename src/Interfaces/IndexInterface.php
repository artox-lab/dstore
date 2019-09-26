<?php
/**
 * Interface of indexes
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Interfaces;

use DStore\Redis\Indexes\State;

interface IndexInterface
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName() : string ;

    /**
     * Value for filtering documents
     *
     * @param DocumentInterface $doc Document
     *
     * @return string|array|State
     */
    public function getNewState(DocumentInterface $doc);

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

}
