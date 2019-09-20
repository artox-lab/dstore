<?php
/**
 * Interface of document saved to store
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Interfaces;

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
    public function getId() : string;

    /**
     * Array of attributes
     *
     * @return array
     */
    public function attributes() : array;

    /**
     * Array of allowed indexes for that document
     *
     * @return array
     */
    public function indexes() : array;

    /**
     * Refs on results of intersection existing indexes (like cache)
     *
     * @return array
     */
    public function indexRefs() : array;

}
