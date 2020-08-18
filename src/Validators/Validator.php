<?php
/**
 * Validator
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Validators;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Collection of asserts
     *
     * @var Collection
     */
    protected $asserts;

    /**
     * List of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Validator constructor.
     *
     * @param Collection $asserts Assert
     */
    public function __construct(Collection $asserts)
    {
        $this->asserts = $asserts;
    }

    /**
     * Validate attrs
     *
     * @param array $attrs List of attributes
     *
     * @return void
     */
    public function validate(array $attrs): void
    {
        foreach ($this->asserts->get() as $attr => $asserts) {
            foreach ($asserts as $assert) {
                if (array_key_exists($attr, $attrs) === false) {
                    $this->addError(sprintf('Value %s does not exist in attributes list', $attr));
                    continue;
                }

                if ($assert instanceof Collection) {
                    $validator = new Validator($assert);
                    $validator->validate($attrs[$attr]);

                    if (empty($errors = $validator->getErrors()) === false) {
                        $this->errors = array_merge($this->errors, $errors);
                    }

                    continue;
                }

                if ($assert instanceof AbstractAssert) {
                    if ($assert->run($attrs[$attr]) === false) {
                        $this->addError($assert->getMessage(), $attr);
                    }

                    continue;
                }

                throw new \RuntimeException(
                    sprintf(
                        'Сlass %s does not implement AbstractAssert or Collection',
                        (is_object($assert) ?? gettype($assert))
                    )
                );
            }
        }
    }

    /**
     * Returns errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return array_values($this->errors);
    }

    /**
     * Add validation error
     *
     * @param string      $error Error
     * @param string|null $attr  Attribute
     *
     * @return void
     */
    protected function addError(string $error, string $attr = null): void
    {
        if (empty($error) === true) {
            return;
        }

        if (empty($attr) === false) {
            $error = $attr . ': ' . $error;
        }

        $this->errors[$error] = $error;
    }

}
