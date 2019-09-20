<?php
/**
 * Registry of keys where documents stored
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace DStore\Redis;

class KeysResolver
{

    /**
     * Make key where documents stored
     *
     * @param string $docType Type of document
     *
     * @return string
     */
    public function makeKey(string $docType) : string
    {
        return sprintf('store:doc:%s', $docType);
    }

    /**
     * Make key of watching changes on document
     *
     * @param string $docType Type of document
     * @param string $id      ID of document
     *
     * @return string
     */
    public function makeWatchingKey(string $docType, string $id) : string
    {
        return sprintf('store:watching:%s:%s', $docType, $id);
    }

    /**
     * Make index key
     *
     * @param string $docType Type of document
     * @param string $index   Index name
     *
     * @return string
     */
    public function makeIndexKey(string $docType, string $index) : string
    {
        return sprintf('%s:index:%s', $this->makeKey($docType), $index);
    }

}
