<?php
/**
 * AbstractAssert
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class AbstractAssert
 */
abstract class AbstractAssert
{
    /**
     * Error message
     *
     * @var string
     */
    protected $message = 'Validation failed';

    /**
     * Assert options
     *
     * @var array
     */
    protected $options;

    /**
     * AbstractAssert constructor.
     *
     * @param array $options Assert options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Run value asserting
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    abstract public function run($value): bool;

    /**
     * Returns error message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

}
