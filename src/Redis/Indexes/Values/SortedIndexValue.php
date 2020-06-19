<?php
/**
 * SortedIndexValue
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Values;

use ArtoxLab\DStore\Interfaces\SortedIndexValueInterface;

class SortedIndexValue implements SortedIndexValueInterface
{
    /**
     * Score
     *
     * @var int
     */
    protected $score = 0;

    /**
     * Value
     *
     * @var mixed
     */
    protected $value;

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
     * Set value
     *
     * @param int $value Value
     *
     * @return void
     */
    public function setParam(int $value): void
    {
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
     * @return string
     */
    public function getValue(): string
    {
        if (empty($this->value) === true) {
            throw new \RuntimeException("Value of SortedIndexValue can't be a empty");
        }

        return (string) $this->value;
    }

}
