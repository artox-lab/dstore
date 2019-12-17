<?php
/**
 * Index: filtering products by slug
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Redis\Indexes\ListIndex;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\Indexes\DictionaryIndex;
use ArtoxLab\DStore\Examples\Domain\Entities\Brand;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

class IndexBySlug extends DictionaryIndex
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName(): string
    {
        return 'by_slug';
    }

    /**
     * Value for filtering documents
     *
     * @param Product|DocumentInterface $doc Document
     *
     * @return string|array|State
     */
    public function getState(DocumentInterface $doc)
    {
        $attr = $doc->getDocAttributes();
        return $this->state->new($attr['slug']);
    }

}
