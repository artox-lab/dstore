<?php
/**
 * AbstractEntityBuilder
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

use ArtoxLab\DStore\Interfaces\SerializerInterface;
use ArtoxLab\Entities\Entity;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @return Assert\Collection
     */
    abstract protected function getRules(): Assert\Collection;

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
        $validator = Validation::createValidator();
        $errors    = $validator->validate($attrs, $this->getRules());

        if (count($errors) > 0) {
            $this->logger->error($this->buildLogMessage($attrs, $errors));
            return false;
        }

        return true;
    }

    /**
     * Builds error message for logging
     *
     * @param array                            $attrs  Attributes
     * @param ConstraintViolationListInterface $errors Validation errors
     *
     * @return string
     */
    protected function buildLogMessage(array $attrs, ConstraintViolationListInterface $errors) : string
    {
        $message = sprintf(
            '%s got invalid attributes %s, errors: ',
            static::class,
            $this->serializer->serialize($attrs)
        );

        $message .= implode(
            ', ',
            array_map(
                function (ConstraintViolationInterface $error): string {
                    return $error->getMessage();
                },
                $errors
            )
        );

        return $message;
    }

}
