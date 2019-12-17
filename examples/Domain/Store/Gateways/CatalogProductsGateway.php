<?php
/**
 * Gateway of catalog products
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Examples\Domain\Store\Gateways;


use ArtoxLab\DStore\Redis\KeysResolver;
use ArtoxLab\DStore\Examples\Domain\Entities\Product;
use ArtoxLab\DStore\Examples\Domain\Store\Documents\ProductEntityBuilder;
use Predis\Client;
use Predis\ClientInterface;

class CatalogProductsGateway
{
    /**
     * Redis client
     *
     * @var Client
     */
    protected $redis;

    /**
     * Registry of keys
     *
     * @var KeysResolver
     */
    protected $keys;

    /**
     * Gateway of brands
     *
     * @var BrandsGateway
     */
    protected $brands;

    /**
     * Builder of product entity
     *
     * @var ProductEntityBuilder
     */
    protected $builder;

    /**
     * AbstractGateway constructor.
     *
     * @param ClientInterface $redis  Redis
     * @param KeysResolver    $keys   Registry of keys
     * @param BrandsGateway   $brands Gateway of brands
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys, BrandsGateway $brands)
    {
        $this->redis  = $redis;
        $this->keys   = $keys;
        $this->brands = $brands;

        $this->builder = new ProductEntityBuilder();
    }

    /**
     * Getting product by ID from store
     *
     * @param int $productId ID of product
     *
     * @return Product|null
     */
    public function getById(int $productId) : ?Product
    {
        $data = $this->redis->hget($this->keys->makeKey('product'), (string) $productId);

        if (empty($data) === true) {
            return null;
        }

        $attrs = json_decode($data, true);

        $this->builder->create($attrs);

        return $this->builder->getProduct();
    }

    /**
     * Getting product by ID from store with info about brand
     *
     * @param int $productId ID of product
     *
     * @return Product|null
     */
    public function findByIdWithBrand(int $productId) : ?Product
    {
        $data = $this->redis->hget($this->keys->makeKey('product'), (string) $productId);

        if (empty($data) === true) {
            return null;
        }

        $attrs = json_decode($data, true);

        $this->builder->create($attrs);
        $this->builder->addBrand($this->brands->getById(1));

        return $this->builder->getProduct();
    }

    /**
     * Finding all products associated with specified brand
     *
     * @param int $brandId Brand's ID
     *
     * @return array
     */
    public function findAllByBrandId(int $brandId) : array
    {
        if (empty($brandId) === true) {
            return [];
        }

        $productIds = $this->redis->smembers($this->keys->makeIndexKey('product', 'by_brand_id'));

        if (empty($productIds) === true) {
            return [];
        }

        $data = $this->redis->hmget($this->keys->makeKey('product'), $productIds);

        if (empty($data) === true) {
            return [];
        }

        return array_map(
            function (string $json) : ?Product {
                $attrs = json_decode($json, true);

                $this->builder->create($attrs);

                return $this->builder->getProduct();
            },
            $data
        );
    }

}
