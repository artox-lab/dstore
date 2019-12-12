<?php
/**
 * Implementation of Brands gateway
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Gateways;

use ArtoxLab\DStore\Redis\PersistGateway;
use ArtoxLab\DStore\Examples\Domain\Entities\Brand as BrandEntity;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\Brand;

class BrandsGateway extends PersistGateway
{

    /**
     * Converting attributes to entity
     *
     * @param array $attrs Attributes
     *
     * @return BrandEntity
     */
    protected function toEntity(array $attrs) : ?BrandEntity
    {
        if (empty($attrs) === true) {
            return null;
        }

        return new BrandEntity($attrs['id'], $attrs['title']);
    }

    /**
     * Create or update (if it exists) document
     *
     * @param BrandEntity $entity Entity of brand
     *
     * @return void
     */
    public function createOrUpdate($entity) : void
    {
        $doc = new Brand($entity);
        $this->persist($doc);
    }

    /**
     * Deleting document
     *
     * @param BrandEntity $entity Entity of brand
     *
     * @return void
     */
    public function delete($entity) : void
    {
        $doc = new Brand($entity);
        $this->flush($doc);
    }

    /**
     * Getting brand by ID
     *
     * @param int $brandId ID of brand
     *
     * @return BrandEntity|null
     */
    public function getById(int $brandId) : ?BrandEntity
    {
        $data = $this->redis->hget($this->keys->makeKey('brand'), (string) $brandId);

        if (empty($data) === true) {
            return null;
        }

        $attrs = json_decode($data, true);

        return $this->toEntity($attrs);
    }

}
