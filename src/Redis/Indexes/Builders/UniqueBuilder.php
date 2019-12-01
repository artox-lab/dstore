<?php
/**
 * Unique index builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class UniqueBuilder extends AbstractListBuilder
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

        $actualItems = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        foreach ($actualItems as $item) {
            $transaction->hdel(
                $this->keys->makeIndexKey($dto->docType, $dto->name),
                $item
            );
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

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->hdel(
                $this->keys->makeIndexKey($dto->docType, $dto->name),
                $item
            );
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

        $transaction = $this->beginTransaction($dto);

        foreach ($items as $item) {
            $transaction->hset(
                $this->keys->makeIndexKey($dto->docType, $dto->name),
                $item,
                $dto->docId
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
