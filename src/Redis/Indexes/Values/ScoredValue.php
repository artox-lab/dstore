<?php
/**
 * ScoredValue
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Values;

class ScoredValue
{
    protected $score = 0;

    protected $value;

    /**
     * ScoredValue constructor
     *
     * @param mixed $score Score
     * @param mixed $value Value
     */
    public function __construct($score, $value)
    {
        $this->score = $score;
        $this->value = $value;
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

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
