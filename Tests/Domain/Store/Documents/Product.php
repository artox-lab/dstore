<?php
/**
 * Document of product
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Domain\Store\Documents;


use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Tests\Domain\Entities\Product as ProductEntity;
use ArtoxLab\DStore\Tests\Domain\Store\Documents\Product\IndexByBrandId;
use ArtoxLab\Entities\States\StateItem;

class Product implements DocumentInterface
{
    /**
     * Entity of product
     *
     * @var ProductEntity
     */
    protected $product;

    /**
     * Product constructor.
     *
     * @param ProductEntity $product Entity of product
     */
    public function __construct(ProductEntity $product)
    {
        $this->product = $product;
    }

    /**
     * Getting doc type
     *
     * @return string
     */
    public function getDocType(): string
    {
        return 'product';
    }

    /**
     * Identifier of document
     *
     * @return string
     */
    public function getDocId(): string
    {
        return (string) $this->product->getId();
    }

    /**
     * Array of attributes
     *
     * @return array
     */
    public function getDocAttributes(): array
    {
        return [
            'id'    => $this->product->getId(),
            'title' => $this->product->getTitle(),
        ];
    }

    /**
     * Array of allowed indexes for that document
     *
     * @return array
     */
    public function getDocIndexes(): array
    {
        return [IndexByBrandId::class];
    }

    /**
     * Refs on results of intersection existing indexes (like cache)
     *
     * @return array
     */
    public function getDocReferences(): array
    {
        return [];
    }

    /**
     * Getting brand state
     *
     * @return StateItem
     */
    public function getBrandState() : StateItem
    {
        return $this->product->getReferenceState('brand');
    }

    /**
     * Getting brand score state
     *
     * @return StateItem
     */
    public function getBrandScoreState() : StateItem
    {
        return $this->product->getReferenceState('brandScore');
    }

}
