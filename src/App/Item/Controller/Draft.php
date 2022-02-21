<?php


namespace App\Item\Controller;


use App\Done\Controller\Done;
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
                ->addKeyboardKey('Пока не хочу', '/done', MessageRoute::APPEAR_CALLBACK_ANSWER);
        }

        if (mb_eregi('ничего|закончил|хватит', $text) !== false) {
            $this->setNextResource(null);
            return $this->textResponse("Хорошо, закончили");
        }

        $listId = $this->p('lid');
        if (!$listId) {
            $listId = $this->getState('lid');
            if (!$listId) {
                $userWishlistStorage = new WishlistUserStorage();
                $userListData = $userWishlistStorage->read()->get($this->getUserId(), __METHOD__);
                if (!$userListData) {
                    return $this->textResponse('Я не нашел вишлиста куда добавить. Нужно бы его создать.')
                        ->addKeyboardKey('Создать вишлист', '/wishlist_create');
                }

                $listId = $userListData[WishlistUserStorage::WISHLIST_ID];
            }
        }

        $listData = [];
        if ($listId) {
            $listStorage = new WishlistStorage();
            $listData = $listStorage->read()->get($listId, __METHOD__);
        }

        $storage = new ItemStorage();
        $id = Uuid::v4();

        $writeResult = $storage->write()->insert($id, [
            ItemStorage::NAME => $this->p('text'),
            ItemStorage::USER_ID => $this->getUserId(),
            ItemStorage::CREATED_AT => time(),
        ], __METHOD__);

        if (!$writeResult) {
            return $this->textResponse('Не удалось записать.');
        }

        $wishlistStorage = new WishlistStorage();
        $userListData = $wishlistStorage->read()->get($listId, __METHOD__);
        $items = array_merge($listData[WishlistStorage::ITEMS] ?? [], [$id]);

        $wishlistStorage->write()->update($listId, [
            WishlistStorage::ITEMS => $items
        ], __METHOD__);

        $text = "Я записал, что ты хочешь: $text\n";
        if (!empty($listData)) {
            if (!is_array($listData[WishlistStorage::ITEMS])) {
                $listData[WishlistStorage::ITEMS] = [];
            }

            $listData[WishlistStorage::ITEMS][] = $id;

            $text .= "В список \"{$userListData[WishlistStorage::NAME]}\"\n";
        } else {
            $text .= "просто в список твоих желаний, потому что не нашел твоего вишлиста.\n";
        }

        $text .= "Что еще хочешь?";

        return $this->textResponse($text)
            ->addKeyboardKey('Отменить', $this->r(Delete::class), ['iid' => $id],MessageRoute::APPEAR_CALLBACK_ANSWER)
            ->addKeyboardKey('Показать все желания', $this->r(All::class), ['iid' => $id])
            ->addKeyboardKey('Давай закончим.', $this->r(Done::class), [], MessageRoute::APPEAR_CALLBACK_ANSWER);
    }

}