<?php

namespace App\Wishlist\Controller;

use App\Wishlist\Service\WishlistUserStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Wishlist extends TelegramExtendedController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->textResponse($this->getUserId());

        $storage = new WishlistUserStorage();
        $listId = $storage->read()->get($userId, __METHOD__);
        if (!$listId) {
            return  $this->textResponse('У тебя еще не создан вишлист! Создать? /wishlist_create');
        }

        return $this->textResponse("Айди твоего вишлиста " . $listId);

    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->textResponse('Сейчас загружу');
    }

}