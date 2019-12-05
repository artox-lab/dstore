<?php
/**
 * EntityBuilderFactory
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Redis\Exceptions\InvalidBuilderNameException;
use Psr\Log\LoggerInterface;
use ArtoxLab\DStore\Interfaces\SerializerInterface;

class EntityBuilderFactory
{
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * EntityBuilderFactory constructor.
     *
     * @param LoggerInterface     $logger     Logger
     * @param SerializerInterface $serializer Serializer
     */
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger     = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Makes entity builder
     *
     * @param string $builderName Builder class name
     *
     * @return AbstractEntityBuilder
     */
    public function make(string $builderName): AbstractEntityBuilder
    {
        if (class_exists($builderName) === false) {
            throw new InvalidBuilderNameException($builderName);
        }

        return new $builderName($this->logger, $this->serializer);
    }

}
