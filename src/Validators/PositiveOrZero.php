<?php
/**
 * PositiveOrZero
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class PositiveOrZero
 */
class PositiveOrZero extends AbstractAssert
{
    /**
     * Error message
     *
     * @var string
     */
    protected $message = 'This value should be either positive or zero.';

    /**
     * Run value asserting
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    public function run($value): bool
    {
        if (is_numeric($value) === false || $value < 0) {
            return false;
        }

        return true;
    }

}
