<?php
/**
 * Index: filtering products by brand ID
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Redis\Indexes\ListIndex;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Examples\Domain\Entities\Brand;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

class IndexByBrandId extends ListIndex
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName(): string
    {
        return 'by_brand_id';
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
        return $this->state->new(
            $doc->getBrandState(),
            function (Brand $brand) : int {
                return $brand->getId();
            }
        );
    }

}
