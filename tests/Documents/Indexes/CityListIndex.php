<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

use DStore\Interfaces\DocumentInterface;
use DStore\Tests\City;

declare(strict_types=1);

class CityListIndex extends \DStore\Redis\Indexes\ListIndex
{
    /**
     * @var \DStore\Tests\Place
     */
    protected $place;

    /**
     * Name of index, use only letters in snake_case
     *
     * @return string
     */
    public function getName(): string
    {
        return 'by_city_id';
    }

    /**
     * Value for filtering documents
     *
     * @param PlaceDoc|DocumentInterface $doc Document
     *
     * @return string|array
     */
    public function getNewState(DocumentInterface $doc)
    {
        return $this->state->new(
            $doc->place->cities,
            function (City $city) : string {
                return (string) $city->id;
            }
        );
    }

}
