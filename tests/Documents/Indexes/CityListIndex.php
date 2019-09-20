<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

use DStore\Tests\City;

declare(strict_types=1);

class CityListIndex
{
    /**
     * @var \DStore\Tests\Place
     */
    protected $place;

    public function index(\DStore\Tests\Place $place)
    {

    }

    protected function values(\DStore\Tests\Place $place) : array
    {
        return array_map(
            function (City $city) : int {
                return $city->id;
            },
            $this->place->cities
        );
    }
}