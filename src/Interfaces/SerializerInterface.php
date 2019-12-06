<?php
/**
 * SerializerInterface
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Interfaces;


interface SerializerInterface
{

    /**
     * Serialize data
     *
     * @param mixed $data Data
     *
     * @return string
     */
    public function serialize($data): string;

    /**
     * Unserialize data
     *
     * @param string $data Data
     *
     * @return mixed
     */
    public function deserialize(string $data);

}
