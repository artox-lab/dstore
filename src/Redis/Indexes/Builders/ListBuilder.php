<?php
/**
 * Index of list (set) builder
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class ListBuilder extends AbstractListBuilder
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
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $value), $dto->docId);
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
     * @param IndexDto $dto   Index dto
     * @param array    $items Deleted items
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
            $transaction->srem($this->keys->makeIndexKey($dto->docType, $dto->name, $item), $dto->docId);
            $transaction->srem($this->getSysKey($dto), $item);
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
     * @param IndexDto $dto   Index dto
     * @param array    $items Added items
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
            $transaction->sadd(
                $this->keys->makeIndexKey($dto->docType, $dto->name, $item),
                [$dto->docId]
            );

            $transaction->sadd($this->getSysKey($dto), $item);
        }

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
