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
use ArtoxLab\DStore\Redis\Indexes\Values\ScoredValue;
use ArtoxLab\DStore\Examples\Domain\Entities\BrandRubricScore;
use ArtoxLab\DStore\Examples\Domain\Entities\BrandScore;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Product;

class SortedIndexByBrandIdAndRubricId extends SortedListIndex
{

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName(): string
    {
        return 'sorted_by_brand_id_and_rubric_id';
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
        $score = $doc->getBrandRubricScoreState();
        return $this->state->new(
            $score,
            function (BrandRubricScore $score) : ScoredValue {
                $scoredValue = (new ScoredValue($score->getScore()))
                    ->setParam('rubric_id', $score->getRubric()->getId())
                    ->setParam('brand_id', $score->getBrand()->getId());

                return $scoredValue;
            }
        );
    }

}
