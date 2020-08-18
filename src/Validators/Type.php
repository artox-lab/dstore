<?php
/**
 * Type
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class Type
 */
class Type extends AbstractAssert
{

    /**
     * Run value asserting
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    public function run($value): bool
    {
        if (isset($this->options['type']) === false) {
            throw new \RuntimeException('type not initialized');
        }

        if (gettype($value) === $this->options['type']) {
            return true;
        }

        $this->message = sprintf('Value "%s" should be of type %s', $value, $this->options['type']);

        return true;
    }

}
