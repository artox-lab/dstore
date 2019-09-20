<?php
/**
 * One to one index: one doc related with another one doc
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes;

use DStore\Interfaces\DocumentInterface;
use DStore\Interfaces\IndexInterface;

abstract class SetIndex implements IndexInterface
{

    /**
     * Relations between documents
     *
     * @return array
     */
    public function relations(): array
    {
        return [
            Place::class  => ['by_place_id' => 'id'],
            Rubric::class => ['by_rubric_id' => 'id'],
        ];
    }

    /**
     * Indexing of document
     *
     * @param DocumentInterface $doc Document
     *
     * @return void
     */
    public function index(DocumentInterface $doc): void
    {
        // TODO: Implement index() method.
    }

}
