<?php
/**
 * Abstract redis-based gateway
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Interfaces\SerializerInterface;
use Predis\Client;
use Predis\ClientInterface;

class AbstractGateway
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
     * Entity builder factory
     *
     * @var EntityBuilderFactory
     */
    protected $entityBuilder;

    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * AbstractGateway constructor.
     *
     * @param ClientInterface      $redis         Redis
     * @param KeysResolver         $keys          Registry of keys
     * @param EntityBuilderFactory $entityBuilder Entity builder factory
     * @param SerializerInterface  $serializer    Serializer
     */
    public function __construct(
        ClientInterface $redis,
        KeysResolver $keys,
        EntityBuilderFactory $entityBuilder,
        SerializerInterface $serializer
    ) {
        $this->redis         = $redis;
        $this->keys          = $keys;
        $this->entityBuilder = $entityBuilder;
        $this->serializer    = $serializer;
    }

}
