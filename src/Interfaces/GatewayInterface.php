<?php
/**
 * Interface of gateways (repositories)
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Interfaces;

interface GatewayInterface
{

    /**
     * Create or update (if it exists) document
     *
     * @param mixed $dto DTO
     *
     * @return mixed
     */
    public function createOrUpdate($dto);

    /**
     * Deleting document
     *
     * @param mixed $dto DTO
     *
     * @return void
     */
    public function delete($dto) : void;

}
