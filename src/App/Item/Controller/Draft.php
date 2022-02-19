<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Draft extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource('/item_draft');

        $text = $this->p('text');

        if (!$text) {
            return $this->textResponse('Что ты хочешь? Напиши!')
                ->addKeyboardKey('Пока не хочу','/done', MessageRoute::APPEAR_CALLBACK_ANSWER);
        }

        if (mb_eregi('ничего|закончил|хватит', $text) !== false) {
            $this->setNextResource(null);
            return $this->textResponse("Хорошо, закончили");
        }

        $listId = $this->p('lid');
        if ($listId) {
            $listId = $this->getState('lid');
            if (!$listId) {
                $userWishlistStorage = new WishlistUserStorage();
                $listData = $userWishlistStorage->read()->get($this->getUserId(), __METHOD__);
                if (!$listData) {
                    return $this->textResponse('Я не нашел вишлиста куда добавить. Нужно бы его создать.')
                        ->addKeyboardKey('Создать вишлист','/wishlist_create');
                }

                $listId = $listData[WishlistUserStorage::WISHLIST_ID];
            }
        }

        $storage = new ItemStorage();
        $id = Uuid::v4();

        $writeResult = $storage->write()->insert($id, [
            ItemStorage::NAME => $this->p('text'),
            ItemStorage::USER_ID => $this->getUserId(),
            ItemStorage::CREATED_AT => time(),
        ], __METHOD__);

        if ($writeResult) {
            $wishlistStorage = new WishlistStorage();
            $listData = $wishlistStorage->read()->get($listId, __METHOD__);
            $items = array_merge($listDatap[WishlistStorage::ITEMS]  ?? [], [$id]);

            $wishlistStorage->write()->update($listId, [
                WishlistStorage::ITEMS => $items
            ], __METHOD__);

            return $this->textResponse("Я записал, что ты хочешь: $text\nВ список \"{$listData[WishlistStorage::NAME]}\" \nЧто еще хочешь?"
            )
                ->addKeyboardKey('Отменить', '/item_delete', [ 'iid' => $id ],
                    MessageRoute::APPEAR_CALLBACK_ANSWER
                )
                ->addKeyboardKey('Показать все желания', '/item_all', [ 'iid' => $id ])
                ->addKeyboardKey('Давай закончим.', '/done', [], MessageRoute::APPEAR_CALLBACK_ANSWER)
                ;
        }

        return $this->textResponse('Не удалось записать.');
    }

}