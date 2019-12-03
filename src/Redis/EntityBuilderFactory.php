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

class EntityBuilderFactory
{
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * EntityBuilderFactory constructor.
     *
     * @param LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        return new $builderName($this->logger);
    }

}
