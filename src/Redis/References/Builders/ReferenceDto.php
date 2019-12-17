<?php
/**
 * DTO of reference
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\References\Builders;

class ReferenceDto
{
    /**
     * List name
     *
     * @var string
     */
    public $name;

    /**
     * Document type
     *
     * @var string
     */
    public $docType;

    /**
     * Id of document
     *
     * @var string
     */
    public $docId;
}
