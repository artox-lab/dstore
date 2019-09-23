<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Tests;

use ArtoxLab\Domain\RelatedCollection;
use ArtoxLab\Domain\RelatedItem;

class Place
{
    public $id;

    public $title;

    /**
     * @var Address
     */
    public $address;

    /**
     * @var Rubric[]|RelatedCollection
     */
    public $rubrics;

    /**
     * @var Service[]|RelatedCollection
     */
    public $services;

    /**
     * @var City[]|RelatedItem
     */
    public $cities;

    /**
     * @var Sort[]
     */
    public $sorts;
}