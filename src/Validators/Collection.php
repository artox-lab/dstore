<?php
/**
 * Collection
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class Collection
 */
class Collection
{
    /**
     * List of asserts
     *
     * @var array
     */
    protected $asserts;

    /**
     * Collection constructor.
     *
     * @param array $asserts List of asserts
     */
    public function __construct(array $asserts = [])
    {
        $this->asserts = $asserts;
    }

    /**
     * Returns list of asserts
     *
     * @return array
     */
    public function get(): array
    {
        return $this->asserts;
    }

}
