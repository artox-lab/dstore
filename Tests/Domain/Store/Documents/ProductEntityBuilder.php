<?php
/**
 * Builder of product entity
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Domain\Store\Documents;

use ArtoxLab\DStore\Tests\Domain\Entities\Brand;
use ArtoxLab\DStore\Tests\Domain\Entities\Product as ProductEntity;
use ArtoxLab\Entities\RelatedItem;

class ProductEntityBuilder
{
    /**
     * Entity of product
     *
     * @var ProductEntity
     */
    protected $product;

    /**
     * Creating entity from attributes
     *
     * @param array $attrs Attributes
     *
     * @return void
     */
    public function create(array $attrs) : void
    {
        if (empty($attrs) === true) {
            return;
        }

        $this->product = new ProductEntity($attrs['id'], $attrs['title']);
    }

    /**
     * Adding entity of brand to product
     *
     * @param Brand|null $brand Brand of product
     *
     * @return void
     */
    public function addBrand(?Brand $brand) : void
    {
        if (empty($this->product) === true || empty($brand) === true) {
            return;
        }

        $this->product->setReferenceState('brand', new RelatedItem($brand));
    }

    /**
     * Getting entity of product
     *
     * @return ProductEntity|null
     */
    public function getProduct() : ?ProductEntity
    {
        return $this->product;
    }

}
