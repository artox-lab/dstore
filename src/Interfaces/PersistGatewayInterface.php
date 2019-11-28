<?php
/**
 * Interface of gateways (repositories)
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;

interface PersistGatewayInterface
{

    /**
     * Create or update (if it exists) entity
     *
     * @param mixed $entity Entity
     *
     * @return void
     */
    public function createOrUpdate($entity) : void;

    /**
     * Deleting document
     *
     * @param mixed $entity Entity
     *
     * @return void
     */
    public function delete($entity) : void;

}
