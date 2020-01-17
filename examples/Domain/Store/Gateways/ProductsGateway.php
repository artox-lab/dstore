<?php
/**
 * Implementation of Products gateway
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Gateways;

use ArtoxLab\DStore\Redis\PersistGateway;
use ArtoxLab\DStore\Examples\Domain\Entities\Product;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Product as ProductDoc;

class ProductsGateway extends PersistGateway
{

    /**
     * Create or update (if it exists) document
     *
     * @param Product $entity Entity of product
     *
     * @return void
     */
    public function createOrUpdate($entity) : void
    {
        $doc = new ProductDoc($entity);
        $this->persist($doc);
    }

    /**
     * Deleting document
     *
     * @param Product $entity Entity of product
     *
     * @return void
     */
    public function delete($entity) : void
    {
        $doc = new ProductDoc($entity);
        $this->flush($doc);
    }

}
