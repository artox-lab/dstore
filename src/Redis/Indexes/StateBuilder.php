<?php
/**
 * State builder
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis\Indexes;


use ArtoxLab\Domain\RelatedCollection;
use ArtoxLab\Domain\RelatedItem;

class StateBuilder
{

    /**
     * Building state
     *
     * @param string        $state         New state
     * @param callable|null $valueResolver Value resolver
     *
     * @return State
     */
    public function new($state, ?callable $valueResolver = null) : State
    {
        if ($state instanceof RelatedItem) {
            return new State([$valueResolver($state->get())], [], true);
        }

        if ($state instanceof RelatedCollection) {
            return new State(
                array_map($valueResolver, $state->getAddedItems()),
                array_map($valueResolver, $state->getDeletedItems()),
                $state->isFlushed()
            );
        }

        return new State([(string) $state], [], true);
    }

}
