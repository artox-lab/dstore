<?php
/**
 * Interface of references
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;

use ArtoxLab\DStore\Redis\Indexes\State;

interface ReferenceInterface
{

    /**
     * Name of reference, use only letters in snake_case
     *
     * @return string
     */
    public function getName() : string;

    /**
     * State of value
     *
     * @param DocumentInterface $doc Document
     *
     * @return string|array|State
     */
    public function getState(DocumentInterface $doc);

    /**
     * Persist reference of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function persist(DocumentInterface $doc) : void;

    /**
     * Flush reference of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function flush(DocumentInterface $doc) : void;

    /**
     * Indicates if the reference should persist/flush.
     *
     * @param DocumentInterface $doc Document
     *
     * @return bool
     */
    public function shouldHandle(DocumentInterface $doc): bool;

}
