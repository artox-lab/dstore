<?php
/**
 * Related entity with entity
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\Domain;

class RelatedItem
{
    /**
     * Related entity
     *
     * @var object
     */
    protected $entity;

    /**
     * Relation was modified
     *
     * @var bool
     */
    protected $isModified = false;

    /**
     * Relation was deleted
     *
     * @var bool
     */
    protected $isDeleted = false;

    /**
     * RelatedItem constructor.
     *
     * @param Entity $entity Related entity
     */
    public function __construct($entity)
    {
        if (empty($entity) === false) {
            $this->entity = $entity;
        }
    }

    /**
     * Update related item
     *
     * @param object $item Related item
     *
     * @return void
     */
    public function update($item) : void
    {
        $this->entity     = $item;
        $this->isModified = true;
    }

    /**
     * Delete relation with item
     *
     * @return void
     */
    public function delete() : void
    {
        $this->entity    = null;
        $this->isDeleted = true;
    }

    /**
     * Is relation modified
     *
     * @return bool
     */
    public function isModified() : bool
    {
        return (bool) $this->isModified;
    }

    /**
     * Is relation deleted
     *
     * @return bool
     */
    public function isDeleted() : bool
    {
        return (bool) $this->isDeleted;
    }

    /**
     * Reset state of changes, use after saving state to store
     *
     * @return void
     */
    public function reset() : void
    {
        $this->isModified = false;
        $this->isDeleted  = false;
    }

}
