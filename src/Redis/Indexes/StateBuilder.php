<?php
/**
 * State builder
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes;

use ArtoxLab\Entities\RelatedCollection;
use ArtoxLab\Entities\RelatedItem;
use RuntimeException as RuntimeException;

class StateBuilder
{

    /**
     * Building state
     *
     * @param mixed         $state         New state
     * @param callable|null $valueResolver Value resolver
     *
     * @return State
     */
    public function new($state, ?callable $valueResolver = null) : State
    {
        if (is_object($state) === true) {
            if (empty($valueResolver) === true) {
                throw new RuntimeException("Value resolver is required for objects.");
            }

            return $this->stateFromObject($state, $valueResolver);
        }

        if (is_array($state) === true) {
            return $this->stateFromArray($state, $valueResolver);
        }

        return new State([(string) $state], [], true);
    }

    /**
     * Make state from object
     *
     * @param object|RelatedItem|RelatedCollection $state         Updated state of document
     * @param callable                             $valueResolver Value resolver
     *
     * @return State
     */
    protected function stateFromObject($state, callable $valueResolver) : State
    {
        if ($state instanceof RelatedCollection) {
            return new State(
                array_map($valueResolver, $state->getAddedItems()),
                array_map($valueResolver, $state->getDeletedItems()),
                $state->isFlushed()
            );
        }

        if ($state instanceof RelatedItem && $state->isModified() === false) {
            return new State([], [], false);
        }

        return new State([$valueResolver($state)], [], true);
    }

    /**
     * Make state from array (always rewrite old state to new state)
     *
     * @param array         $state         New state
     * @param callable|null $valueResolver Value resolver (for array of arrays or array of objects)
     *
     * @return State
     */
    protected function stateFromArray(array $state, ?callable $valueResolver) : State
    {
        $added = [];

        if (empty($state) === false) {
            $tmp = current($state);

            if (is_array($tmp) === true || is_object($tmp) === true) {
                if (empty($valueResolver) === true) {
                    throw new RuntimeException("Value resolver is required for objects.");
                }

                $added = array_map($valueResolver, $state);
            }
        }

        return new State($added, [], true);
    }

}
