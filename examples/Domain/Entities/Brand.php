<?php
/**
 * Brand
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Entities;

use ArtoxLab\Entities\Entity;

class Brand implements Entity
{
    /**
     * ID
     *
     * @var int
     */
    private $id;

    /**
     * Title of brand
     *
     * @var string
     */
    private $title;

    /**
     * Brand constructor.
     *
     * @param int    $id    ID
     * @param string $title Title of brand
     */
    public function __construct(int $id, string $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }

    /**
     * Getting ID
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Getting title of brand
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

}
