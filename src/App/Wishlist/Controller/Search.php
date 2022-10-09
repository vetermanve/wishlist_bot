<?php

namespace App\Wishlist\Controller;

use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Search extends WishlistBaseController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();
//
//        $storage = new WishlistUserStorage();
//
//        $listData = $storage->read()->get($userId, __METHOD__);
//        $listId = $listData[WishlistUserStorage::WISHLIST_ID] ?? null;
//
//        if (!$listId) {
//            return $this->textResponse('У тебя еще не создан вишлист!')
//                ->addKeyboardKey('Создать', $this->r(Create::class))
//            ;
//        }
//
//        $list = (new WishlistStorage())->read()->get($listId, __METHOD__);
//        if ($list) {
//            $text = "У тебя есть вишлист и он называется\n \"" . $list[WishlistStorage::NAME].'"';
//            $buttonText = 'Переименовать';
//        } else {
//            $text = "У тебя есть вишлист, он без названия =( (" . $listId.')';
//            $buttonText = 'Задать имя';
//        }
//
//        return $this->textResponse($text)
//            ->addKeyboardKey($buttonText, $this->r(Name::class), [ 'lid' => $listId, ])
//            ->addKeyboardKey("Добавить желание", $this->r(Draft::class), [ 'lid' => $listId, ])
//            ->addKeyboardKey("Посмотреть желания", $this->r(All::class), [ 'lid' => $listId, ])
//            ->addKeyboardKey("Поделиться, Управлять ссылками.", $this->r(\App\Link\Controller\All::class), [ 'lid' => $listId, ])
//        ;
        return  $this->textResponse($this->getUserId());
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}