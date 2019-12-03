<?php
/**
 * Dictionary index builder
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\Indexes\Builders;

use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class DictionaryBuilder extends OneToOneIndexBuilder
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
        $this->watch($dto);

        $actualItem  = $this->getActualState($dto);
        $transaction = $this->beginTransaction($dto);

        $transaction->hdel(
            $this->keys->makeIndexKey($dto->docType, $dto->name),
            [$actualItem]
        );

        $transaction->hdel($this->getSysKey($dto), [$this->getSysField($dto)]);

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Adding an item to relation
     *
     * @param IndexDto $dto  Index dto
     * @param string   $item Added item
     *
     * @return void
     */
    public function persist(IndexDto $dto, string $item) : void
    {
        if (empty($item) === true) {
            return;
        }

        $this->watch($dto);

        $transaction = $this->beginTransaction($dto);

        $transaction->hset(
            $this->keys->makeIndexKey($dto->docType, $dto->name),
            $item,
            $dto->docId
        );

        $transaction->hset(
            $this->getSysKey($dto),
            $this->getSysField($dto),
            $item
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->persist($dto, $item);
        }
    }

}
