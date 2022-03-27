<?php

namespace App\Wishlist\Controller;

use App\Wishlist\Service\WishlistUserStorage;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Spec\DisplayControl;

class Remove extends TelegramExtendedController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();
        $storage = new WishlistUserStorage();
        $listData = $storage->read()->get($userId, __METHOD__);
        $listId = $listData[WishlistUserStorage::WISHLIST_ID] ?? null;

        if ($listId) {
            $storage->write()->remove($userId, __METHOD__);

            return $this->response()->setText('Твой вишлист удален!: '.$listId)
                ->addKeyboardKey('Создать новый', '/wishlist_create?'.DisplayControl::PARAM_SET_APPEARANCE.'='.MessageRoute::APPEAR_NEW_MESSAGE)
                ;

        }

        return $this->textResponse("В данный момент у тебя нет вишлиста");
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }


}