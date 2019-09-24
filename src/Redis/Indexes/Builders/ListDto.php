<?php
/**
 * DTO of list index
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes\Builders;

class ListDto
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