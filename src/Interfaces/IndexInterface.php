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
     * ID of document
     *
     * @return string
     */
    public function getDocId() : string ;

    /**
     * Value for filtering documents
     *
     * @return string|array|State
     */
    public function getNewState();

    /**
     * Indexing of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function index(DocumentInterface $doc) : void;

}
