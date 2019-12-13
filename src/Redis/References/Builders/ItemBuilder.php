<?php
/**
 * Reference of item builder
 *
 * @author Irina Volosevich <i.volosevich@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Redis\References\Builders;

use ArtoxLab\DStore\Redis\References\Builders\ReferenceDto;
use Predis\CommunicationException;
use Predis\Response\ServerException;
use Predis\Transaction\AbortedMultiExecException;

class ItemBuilder extends ReferenceItemBuilder
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
            [$this->keys->makeReferenceField($dto->docId, $dto->name)]
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->flush($dto);
        }
    }

    /**
     * Adding item
     *
     * @param ReferenceDto $dto  Reference dto
     * @param string       $item Added item
     *
     * @return void
     */
    public function persist(ReferenceDto $dto, ?string $item) : void
    {
        if (empty($item) === true) {
            return;
        }
        
        $transaction = $this->beginTransaction($dto);

        $transaction->hset(
            $this->keys->makeReferenceKey($dto->docType),
            $this->keys->makeReferenceField($dto->docId, $dto->name),
            $item
        );

        try {
            $transaction->execute();
        } catch (AbortedMultiExecException | CommunicationException | ServerException $exception) {
            $this->add($dto, $items);
        }
    }

}
