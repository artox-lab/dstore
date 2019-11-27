<?php
/**
 * Sorted index: one doc linked to other documents
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use ArtoxLab\DStore\Redis\Indexes\Values\SortedIndexValue;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class SortedListBuilder extends AbstractListBuilder
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
        $actual      = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        foreach ($actual as $value) {
            $transaction->zrem($this->keys->makeIndexKey($dto->docType, $dto->name, $value), $dto->docId);
            $transaction->del($this->getSysKey($dto));
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Deleting some items from list
     *
     * @param ListDto            $dto   List dto
     * @param SortedIndexValue[] $items Added items
     *
     * @return void
     */
    protected function delete(ListDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->zrem($this->keys->makeIndexKey($dto->docType, $dto->name, $item->getValue()), $dto->docId);
            $transaction->del($this->getSysKey($dto));
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
     * @param ListDto            $dto   List dto
     * @param SortedIndexValue[] $items Added items
     *
     * @return void
     */
    public function add(ListDto $dto, array $items) : void
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

            $transaction->sadd($this->getSysKey($dto), $item->getValue());
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
