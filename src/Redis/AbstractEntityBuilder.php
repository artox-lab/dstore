<?php
/**
 * AbstractEntityBuilder
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Interfaces\SerializerInterface;
use ArtoxLab\DStore\Validators\Collection;
use ArtoxLab\DStore\Validators\Validator;
use ArtoxLab\Entities\Entity;
use Psr\Log\LoggerInterface;

abstract class AbstractEntityBuilder
{
    /**
     * Entity
     *
     * @var Entity
     */
    protected $entity;

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
     * AbstractEntityBuilder constructor.
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
     * Creating entity from attributes
     *
     * @param array $attrs Attributes
     *
     * @return void
     */
    public function create(array $attrs) : void
    {
        if ($this->validate($attrs) === false) {
            return;
        }

        $this->entity = $this->makeEntity($attrs);
    }

    /**
     * Returns entity
     *
     * @return mixed
     */
    abstract public function getEntity();

    /**
     * Validation rules
     *
     * @return Collection
     */
    abstract protected function getRules(): Collection;

    /**
     * Returns new object of entity
     *
     * @param array $attrs attributes
     *
     * @return Entity
     */
    abstract protected function makeEntity(array $attrs): Entity;

    /**
     * Validation of attributes
     *
     * @param array $attrs Attributes
     *
     * @return bool
     */
    protected function validate(array $attrs): bool
    {
        $validator = new Validator($this->getRules());
        $validator->validate($attrs);

        if (empty($errors = $validator->getErrors()) === false) {
            $this->logger->error($this->buildLogMessage($attrs, $errors));
            return false;
        }

        return true;
    }

    /**
     * Builds error message for logging
     *
     * @param array $attrs  Attributes
     * @param array $errors Validation errors
     *
     * @return string
     */
    protected function buildLogMessage(array $attrs, array $errors) : string
    {
        $message = sprintf(
            '%s got invalid attributes %s, errors: ',
            static::class,
            $this->serializer->serialize($attrs)
        );

        foreach ($errors as $error) {
            $message .= $error . PHP_EOL;
        }

        return $message;
    }

}
