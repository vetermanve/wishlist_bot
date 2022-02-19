<?php

namespace App\Wishlist\Controller;

use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Wishlist extends TelegramExtendedController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();

        $storage = new WishlistUserStorage();

        $listData = $storage->read()->get($userId, __METHOD__);
        $listId = $listData[WishlistUserStorage::WISHLIST_ID] ?? null;

        if (!$listId) {
            return  $this->textResponse('У тебя еще не создан вишлист!')
                ->addKeyboardKey('Создать', '/wishlist_create')
            ;
        }

        $list = (new WishlistStorage())->read()->get($listId, __METHOD__);
        if ($list) {
            $text = "У тебя есть вишлист и он называется\n \"" . $list[WishlistStorage::NAME].'"';
            $buttonText = 'Переименовать';
        } else {
            $text = "У тебя есть вишлист, он без названия =( (" . $listId.')';
            $buttonText = 'Задать имя';
        }

        return $this->textResponse($text)
            ->addKeyboardKey($buttonText, '/wishlist_name', [ 'lid' => $listId, ])
            ;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}