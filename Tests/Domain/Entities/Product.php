<?php
/**
 * Entity: product
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Domain\Entities;

use ArtoxLab\Entities\Entity;
use ArtoxLab\Entities\RelatedCollection;
use ArtoxLab\Entities\RelatedItem;

class Product implements Entity
{
    /**
     * ID
     *
     * @var int
     */
    private $id;

    /**
     * Title of product
     *
     * @var string
     */
    private $title;

    /**
     * Brand of product
     *
     * @var RelatedItem|null
     */
    protected $brand;

    /**
     * Product constructor.
     *
     * @param int    $id    ID
     * @param string $title Title of product
     */
    public function __construct(int $id, string $title)
    {
        $this->id    = $id;
        $this->title = $title;

        $this->brand = new RelatedItem(null);
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
     * Getting title of product
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Change brand of product
     *
     * @param Brand $brand Brand of product
     *
     * @return void
     */
    public function changeBrand(Brand $brand) : void
    {
        $this->brand->update($brand);
    }

    /**
     * Getting brand of product
     *
     * @return Brand|null
     */
    public function getBrand() : ?Brand
    {
        return $this->brand->get();
    }

    /**
     * Setting state of reference
     *
     * @param string                        $name  Name of reference
     * @param RelatedItem|RelatedCollection $state State
     *
     * @return void
     */
    public function setReferenceState(string $name, $state)
    {
        if (property_exists($this, $name) === false) {
            throw new \RuntimeException(sprintf('Invalid name of reference %s', $name));
        }

        $this->$name = $state;
    }

    /**
     * Getting state of reference
     *
     * @param string $name Name of reference
     *
     * @return mixed
     */
    public function getReferenceState(string $name)
    {
        if (property_exists($this, $name) === false) {
            throw new \RuntimeException(sprintf('Invalid name of reference %s', $name));
        }

        return $this->$name;
    }

}
