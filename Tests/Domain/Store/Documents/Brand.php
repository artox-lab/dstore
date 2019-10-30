<?php
/**
 * Document of brand
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Domain\Store\Documents;

use ArtoxLab\DStore\Interfaces\DocumentInterface;
use ArtoxLab\DStore\Tests\Domain\Entities\Brand as BrandEntity;
use ArtoxLab\DStore\Tests\Domain\Entities\Product as ProductEntity;
use ArtoxLab\DStore\Tests\Domain\Store\Documents\Product\IndexByBrandId;
use ArtoxLab\Entities\RelatedItem;

class Brand implements DocumentInterface
{
    /**
     * Entity of product
     *
     * @var ProductEntity
     */
    protected $brand;

    /**
     * Product constructor.
     *
     * @param BrandEntity $brand Entity of brand
     */
    public function __construct(BrandEntity $brand)
    {
        $this->brand = $brand;
    }

    /**
     * Getting doc type
     *
     * @return string
     */
    public function getDocType(): string
    {
        return 'brand';
    }

    /**
     * Identifier of document
     *
     * @return string
     */
    public function getDocId(): string
    {
        return (string) $this->brand->getId();
    }

    /**
     * Array of attributes
     *
     * @return array
     */
    public function getDocAttributes(): array
    {
        return [
            'id'    => $this->brand->getId(),
            'title' => $this->brand->getTitle(),
        ];
    }

    /**
     * Array of allowed indexes for that document
     *
     * @return array
     */
    public function getDocIndexes(): array
    {
        return [];
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

}
