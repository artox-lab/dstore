<?php
/**
 * Abstract redis-based gateway
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

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
     * AbstractGateway constructor.
     *
     * @param ClientInterface $redis Redis
     * @param KeysResolver    $keys  Registry of keys
     */
    public function __construct(ClientInterface $redis, KeysResolver $keys)
    {
        $this->redis = $redis;
        $this->keys  = $keys;
    }

}
