<?php

namespace App\Wishlist\Controller;

use App\Item\Controller\All;
use App\Item\Controller\Draft;
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
            return $this->textResponse('У тебя еще не создан вишлист!')
                ->addKeyboardKey('Создать', $this->getResourceByClass(Create::class))
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
            ->addKeyboardKey($buttonText, $this->getResourceByClass(Name::class), [ 'lid' => $listId, ])
            ->addKeyboardKey("Добавить желание", $this->getResourceByClass(Draft::class), [ 'lid' => $listId, ])
            ->addKeyboardKey("Посмотреть желания", $this->getResourceByClass(All::class), [ 'lid' => $listId, ])
        ;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}