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

        $attrs = $this->normalizeFields($attrs);

        $this->entity = $this->makeEntity($attrs);
    }

    /**
     * Returns entity
     *
     * @return mixed
     */
    abstract public function getEntity();

    /**
     * Requried fields
     *
     * @return array
     */
    abstract protected function getSchema(): array;

    /**
     * Returns new object of entity
     *
     * @param array $attrs attributes
     *
     * @return Entity
     */
    abstract protected function makeEntity(array $attrs): Entity;

    /**
     * Normilize required fields
     *
     * @param array $attrs attributes
     *
     * @return array
     */
    protected function normalizeFields(array $attrs): array
    {
        foreach ($this->getSchema() as $field => $type) {
            if (empty($type) === true) {
                continue;
            }

            if (is_array($type) === true) {
                $attrs[$type] = $this->normalizeFields($type);
                continue;
            }

            if ($this->isNullAllowed($type) === false) {
                settype($attrs[$field], $type);
                continue;
            }

            if (array_key_exists($field, $attrs) === false) {
                $attrs[$field] = null;
                continue;
            }

            $type = substr($type, 1, (strlen($type) - 1));

            if (in_array($type, ['int', 'float']) === false) {
                settype($attrs[$field], $type);
                continue;
            }

            if ($attrs[$field] === 0) {
                continue;
            }

            if (empty($attrs[$field]) === true) {
                $attrs[$field] = null;
            }
        }

        return $attrs;
    }

    /**
     * Is null allowed in schema field
     *
     * @param string $type Field type
     *
     * @return bool
     */
    protected function isNullAllowed(string $type): bool
    {
        if (empty($type) === true) {
            return false;
        }

        return $type[0] === '?';
    }

    /**
     * Validation of attributes
     *
     * @param array $attrs Attributes
     *
     * @return bool
     */
    protected function validate(array $attrs): bool
    {
        $errors = [];

        foreach ($this->getSchema() as $field => $type) {
            if ($this->isNullAllowed($type) === true) {
                continue;
            }

            if (array_key_exists($field, $attrs) === false) {
                $errors[] = sprintf('field %s is required', $field);
            }
        }

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
