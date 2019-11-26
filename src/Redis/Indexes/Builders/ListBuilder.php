<?php
/**
 * Index of list (set) builder
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\State;
use ArtoxLab\DStore\Redis\KeysResolver;
use Predis\Client;
use Predis\ClientInterface;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;
use Predis\Transaction\MultiExec;

class ListBuilder extends AbstractListBuilder
{

    /**
     * Flush actual state, be careful
     *
     * @param ListDto $dto List dto
     *
     * @return void
     */
    protected function flush(ListDto $dto) : void
    {
        $this->watch($dto);

        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        foreach ($actual as $value) {
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $value), $dto->docId);
        }

        $transaction->hdel($this->getSysHashKey($dto), [$this->getSysField($dto)]);

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Deleting some items from list
     *
     * @param ListDto $dto   List dto
     * @param array   $items Deleted items
     *
     * @return void
     */
    protected function delete(ListDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $this->watch($dto);

        $actual = $this->getActualState($dto);

        if (empty($actual) === true) {
            return;
        }

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $item), $dto->docId);
        }

        $actual = array_diff($actual, $items);

        if (empty($actual) === false) {
            $transaction->hset($this->getSysHashKey($dto), $this->getSysField($dto), json_encode($actual));
        } else {
            $transaction->hdel($this->getSysHashKey($dto), [$this->getSysField($dto)]);
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->delete($dto, $items);
        }
    }

    /**
     * Adding some items to list
     *
     * @param ListDto $dto   List dto
     * @param array   $items Added items
     *
     * @return void
     */
    public function add(ListDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $this->watch($dto);

        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        $items = array_diff($items, $actual);

        foreach ($items as $item) {
            $transaction->sadd(
                $this->keys->makeIndexKey($dto->docType, $dto->name, $item),
                [$dto->docId]
            );
        }

        $transaction->hset(
            $this->getSysHashKey($dto),
            $this->getSysField($dto),
            json_encode(array_merge($actual, $items))
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
