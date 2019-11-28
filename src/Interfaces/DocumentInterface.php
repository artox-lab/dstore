<?php
/**
 * Interface of document saved to store
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;

interface DocumentInterface
{

    /**
     * Getting doc type
     *
     * @return string
     */
    public function getDocType() : string;

    /**
     * Identifier of document
     *
     * @return string
     */
    public function getDocId() : string;

    /**
     * Array of attributes
     *
     * @return array
     */
    public function getDocAttributes() : array;

    /**
     * Array of allowed indexes for that document
     *
     * @return array
     */
    public function getDocIndexes() : array;

    /**
     * Refs on results of intersection existing indexes (like cache)
     *
     * @return array
     */
    public function getDocReferences() : array;

}
