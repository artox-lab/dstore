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
     * Make temporary key
     *
     * @param string $hashName Hashed name of key
     *
     * @return string
     */
    public function makeTemporaryKey(string $hashName): string
    {
        return sprintf('store:tmp:%s', $hashName);
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
     * Make reference field
     *
     * @param string $id      Entity ID
     * @param string $refName Ref name
     *
     * @return string
     */
    public function makeReferenceField(string $id, string $refName) : string
    {
        return $id . ':' . $refName;
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
        return sprintf('store:watching:index:%s:%s:%s', $docType, $id, $index);
    }

    /**
     * Make key of watching changes on document reference
     *
     * @param string $docType   Type of document
     * @param string $id        ID of document
     * @param string $reference Name of reference
     *
     * @return string
     */
    public function makeWatchingOnDocReferenceKey(string $docType, string $id, string $reference): string
    {
        return sprintf('store:watching:reference:%s:%s:%s', $docType, $id, $reference);
    }

    /**
     * Make list of reference fields
     *
     * @param int[]  $ids     Entity ID's list
     * @param string $refName Ref name
     *
     * @return array
     */
    public function mapReferenceFields(array $ids, string $refName): array
    {
        return array_map(
            function (int $id) use ($refName): string {
                return $this->makeReferenceField((string) $id, $refName);
            },
            $ids
        );
    }

}
