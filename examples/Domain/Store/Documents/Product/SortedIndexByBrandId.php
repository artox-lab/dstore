<?php
/**
 * Index: filtering products by brand ID with sorting
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Redis\Indexes\SortedListIndex;
use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\Indexes\Values\SortedIndexValue;
use ArtoxLab\DStore\Examples\Domain\Entities\BrandScore;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

class SortedIndexByBrandId extends SortedListIndex
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName(): string
    {
        return 'sorted_by_brand_id';
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
        $brandScore = $doc->getBrandScoreState();
        return $this->state->new(
            $brandScore,
            function (BrandScore $score) : SortedIndexValue {
                $value = new SortedIndexValue();
                $value->setScore($score->getScore());
                $value->setParam($score->getBrand()->getId());

                return $value;
            }
        );
    }

}
