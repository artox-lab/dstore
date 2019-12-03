<?php
/**
 * InvalidBuilderNameException
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Exceptions;

class InvalidBuilderNameException extends \InvalidArgumentException
{

    /**
     * InvalidBuilderNameException constructor.
     *
     * @param string $builderName Builder class name
     */
    public function __construct(string $builderName)
    {
        parent::__construct(sprintf("Builder with name %s doesn't exist", $builderName));
    }

}
