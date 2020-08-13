<?php
/**
 * SortedIndexValueInterface
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;

interface SortedIndexValueInterface
{

    /**
     * Get score
     *
     * @return mixed
     */
    public function getScore();

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Set score
     *
     * @param int $score Score
     *
     * @return void
     */
    public function setScore(int $score): void;

}
