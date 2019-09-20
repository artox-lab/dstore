<?php
/**
 * todo: comment
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Tests;

class Place
{
    public $id;

    public $title;

    /**
     * @var Address
     */
    public $address;

    /**
     * @var Rubric[]
     */
    public $rubrics;

    /**
     * @var Service[]
     */
    public $services;

    /**
     * @var City[]
     */
    public $cities;

    /**
     * @var Sort[]
     */
    public $sorts;
}