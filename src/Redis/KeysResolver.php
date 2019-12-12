<?php
/**
 * Registry of keys where documents stored
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis;

class KeysResolver
{

    /**
     * Make key where documents stored
     *
     * @param string $docType Type of document
     *
     * @return string
     */
    public function makeKey(string $docType): string
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
    public function makeWatchingOnDocKey(string $docType, string $id): string
    {
        return sprintf('store:watching:%s:%s', $docType, $id);
    }

    /**
     * Make index key
     *
     * @param string $docType Type of document
     * @param string $index   Index name
     * @param array  $values  Values
     *
     * @return string
     */
    public function makeIndexKey(string $docType, string $index, ...$values): string
    {
        $key = sprintf('%s:index:%s', $this->makeKey($docType), $index);

        if (empty($values) === false) {
            $key .= (':' . implode(':', $values));
        }

        return  $key;
    }

    /**
     * Make reference key
     *
     * @param string $docType Type of document
     *
     * @return string
     */
    public function makeReferenceKey(string $docType) : string
    {
        return $this->makeKey($docType) . ':refs';
    }

    /**
     * Make index system key (where we can find actual values)
     *
     * @param string $docType Type of document
     * @param string $id      ID of document
     * @param string $index   Index name
     *
     * @return string
     */
    public function makeIndexSysKey(string $docType, string $id, string $index): string
    {
        return sprintf('store:sys:indexes:%s:%s:%s', $docType, $id, $index);
    }

    /**
     * Make index system hash key (where we can find actual values)
     *
     * @param string $docType Type of document
     *
     * @return string
     */
    public function makeIndexSysHashKey(string $docType): string
    {
        return sprintf('store:sys:%s:indexes', $docType);
    }

    /**
     * Make field of hash where we stored actual state
     *
     * @param string $index   Index name
     * @param string $docType Type of document
     *
     * @return string
     */
    public function makeSysField(string $index, string $docType): string
    {
        return sprintf('%s:%s', $index, $docType);
    }

    /**
     * Make key of watching changes on document index
     *
     * @param string $docType Type of document
     * @param string $id      ID of document
     * @param string $index   Name of index
     *
     * @return string
     */
    public function makeWatchingOnDocIndexKey(string $docType, string $id, string $index): string
    {
        return sprintf('store:watching:%s:%s:%s', $docType, $id, $index);
    }

}
