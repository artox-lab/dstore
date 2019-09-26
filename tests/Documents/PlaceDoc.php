<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

use DStore\Tests\City;
use DStore\Tests\Sort;

declare(strict_types=1);

class PlaceDoc implements \DStore\Interfaces\DocumentInterface
{
    /**
     * @var \DStore\Tests\Place
     */
    public $place;

    public function attributes() : array
    {
        return [
            'id',
            'title',
        ];
    }

    public function relations() : array
    {
        return [
            'address'  => [\DStore\Redis\Relations\HashRelation::class],
            'rubrics'  => \DStore\Redis\Relations\HashRelation::class,
            'services' => \DStore\Redis\Relations\HashRelation::class,
            'cities'   => \DStore\Redis\Relations\HashRelation::class,
        ];
    }

    public function indexes() : array
    {
        return [
            'by_rubric_id' => new ListIndex($this->place->rubrics),
            'by_city_id'   => new ListIndex(
                $this->place->id,
                array_map(
                    function (City $city) : int {
                        return $city->id;
                    },
                    $this->place->cities
                )
            ),
            'catalog_sort' => new SortedListIndex(
                $this->place->id,
                array_reduce(
                    $this->place->sorts,
                    function (array $carry, Sort $sort) : array {
                        $carry[sprintf('rubric:%d:city:%d', $sort->rubric->id, $sort->city->id)] = $sort->rotationGroup;
                        return $carry;
                    },
                    []
                )
            ),
        ];
    }

    /**
     * Getting doc type
     *
     * @return string
     */
    public function getDocType(): string
    {
        // TODO: Implement getDocType() method.
    }

    /**
     * Identifier of document
     *
     * @return string
     */
    public function getId(): string
    {
        // TODO: Implement getId() method.
    }

    /**
     * Refs on results of intersection existing indexes (like cache)
     *
     * @return array
     */
    public function indexRefs(): array
    {
        // TODO: Implement indexRefs() method.
    }
}
