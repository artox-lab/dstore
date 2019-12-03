<?php
/**
 * DTO of index
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

class IndexDto
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
