<?php
/**
 * Reference of list (set) builder
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\References\Builders;

use ArtoxLab\DStore\Redis\References\Builders\ReferenceDto;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class ListBuilder extends ReferenceBuilder
{

    /**
     * Flush actual state, be careful
     *
     * @param ReferenceDto $dto Reference dto
     *
     * @return void
     */
    protected function flush(ReferenceDto $dto) : void
    {
        $transaction = $this->beginTransaction($dto);
        $transaction->hdel(
            $this->keys->makeReferenceKey($dto->docType),
            [$this->keys->makeReferenceFiled($dto->docId, $dto->name)]
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Deleting some items from list
     *
     * @param ReferenceDto $dto   Reference dto
     * @param array        $items Deleted items
     *
     * @return void
     */
    protected function delete(ReferenceDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $actual   = $this->getActualState($dto);
        $newState = array_diff($actual, $items);

        $transaction = $this->beginTransaction($dto);

        if (empty($newState) === false) {
            $transaction->hset(
                $this->keys->makeReferenceKey($dto->docType),
                $this->keys->makeReferenceFiled($dto->docId, $dto->name),
                $this->serializer->serialize($newState)
            );
        } else {
            $transaction->hdel(
                $this->keys->makeReferenceKey($dto->docType),
                [$this->keys->makeReferenceFiled($dto->docId, $dto->name)]
            );
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
     * @param ReferenceDto $dto   Reference dto
     * @param array        $items Added items
     *
     * @return void
     */
    public function add(ReferenceDto $dto, array $items) : void
    {
        if (empty($items) === true) {
            return;
        }

        $actual = $this->getActualState($dto);

        $transaction = $this->beginTransaction($dto);

        $newState = array_merge($actual, $items);

        $transaction->hset(
            $this->keys->makeReferenceKey($dto->docType),
            $this->keys->makeReferenceFiled($dto->docId, $dto->name),
            $this->serializer->serialize($newState)
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
