<?php
/**
 * BrandScore
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Domain\Entities;

use ArtoxLab\Entities\Entity;

class BrandScore implements Entity
{
    /**
     * ID
     *
     * @var int
     */
    protected $score;

    /**
     * Brand entity
     *
     * @var Brand
     */
    protected $brand;

    /**
     * BrandScore constructor.
     *
     * @param mixed $score Score
     * @param Brand $brand Brand entity
     */
    public function __construct($score, Brand $brand)
    {
        $this->score = $score;
        $this->brand = $brand;
    }

    /**
     * Get score
     *
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * Get entity
     *
     * @return Entity
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

}
