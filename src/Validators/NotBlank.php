<?php
/**
 * NotBlank
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class NotBlank
 */
class NotBlank extends AbstractAssert
{
    /**
     * Error message
     *
     * @var string
     */
    protected $message = 'This value should not be blank.';

    /**
     * Run value asserting
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    public function run($value): bool
    {
        if (empty($this->options['allowNull']) === false && is_null($value) === true) {
            return true;
        }

        return !empty($value);
    }

}
