<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

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
     * ID of document
     *
     * @return string
     */
    public function getDocId(): string
    {
        return (string) $this->place->id;
    }

    /**
     * Value for filtering documents
     *
     * @return string|array
     */
    public function getNewState()
    {
        return $this->place->cities;
    }

}
