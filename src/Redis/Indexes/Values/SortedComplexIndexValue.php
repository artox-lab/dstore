<?php
/**
 * SortedComplexIndexValue
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Values;

use ArtoxLab\DStore\Interfaces\SortedIndexValueInterface;

class SortedComplexIndexValue extends ComplexIndexValue implements SortedIndexValueInterface
{
    /**
     * Score
     *
     * @var int
     */
    protected $score = 0;

    /**
     * Set score
     *
     * @param int $score Score
     *
     * @return void
     */
    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

}
