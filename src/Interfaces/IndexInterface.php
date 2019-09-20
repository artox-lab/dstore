<?php
/**
 * Interface of indexes
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Interfaces;

interface IndexInterface
{

    /**
     * Relations between documents
     *
     * @return array
     */
    public function relations() : array;

    /**
     * Indexing of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function index(DocumentInterface $doc) : void;

}
