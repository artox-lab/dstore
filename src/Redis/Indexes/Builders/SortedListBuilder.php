<?php
/**
 * Sorted index of list (set) builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\Values\SortedIndexValue;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class SortedListBuilder extends OneToManyIndexBuilder
{

    /**
     * Flush actual state, be careful
     *
     * @param IndexDto $dto Index dto
     *
     * @return void
     */
    protected function flush(IndexDto $dto) : void
    {
        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        foreach ($actual as $value) {
            $transaction->zrem($this->keys->makeIndexKey($dto->docType, $dto->name, $value), $dto->docId);
        }

        $transaction->del($this->getSysKey($dto));

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Deleting some items from list
     *
     * @param IndexDto           $dto   Index dto
     * @param SortedIndexValue[] $items Added items
     *
     * @return void
     */
    protected function delete(IndexDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->zrem($this->keys->makeIndexKey($dto->docType, $dto->name, $item->getValue()), $dto->docId);
            $transaction->srem($this->getSysKey($dto), $item);
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->delete($dto, $items);
        }
    }

    /**
     * Adding some items to sorted list
     *
     * @param IndexDto           $dto   Index dto
     * @param SortedIndexValue[] $items Added items
     *
     * @return void
     */
    public function add(IndexDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->zadd(
                $this->keys->makeIndexKey($dto->docType, $dto->name, $item->getValue()),
                [$dto->docId => $item->getScore()]
            );

            $transaction->sadd($this->getSysKey($dto), $this->keys->makeSysField($dto->name, (string) $item->getValue()));
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
