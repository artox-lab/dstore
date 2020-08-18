<?php
/**
 * NotNull
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class NotNull
 */
class NotNull extends AbstractAssert
{
    /**
     * Error message
     *
     * @var string
     */
    protected $message = 'This value should not be null.';

    /**
     * Run value asserting
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    public function run($value): bool
    {
        return !is_null($value);
    }

}
