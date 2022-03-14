<?php

namespace App\Start\Controller;

use App\Item\Controller\All;
use App\Item\Controller\Draft;
use App\Item\Controller\WList;
use App\Link\Service\LinkStorage;
use App\Wishlist\Controller\Search;
use App\Wishlist\Controller\Wishlist;
use App\Wishlist\Service\WishlistStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Start extends TelegramExtendedController
{

    public function text_message(): ?TelegramResponse
    {
        $text = $this->p('text');
        if ($text) {
            $linkId = $text;

            $linkStorage = new LinkStorage();
            $link = $linkStorage->read()->get($linkId, __METHOD__);
            if ($link) {
               $wlId = $link[LinkStorage::WL_ID];
               $wlStorage = new WishlistStorage();
               $list = $wlStorage->read()->get($wlId, __METHOD__);
               if ($list) {
                   $text = "Я вижу пришел взглянуть на вишлист \n".
                       '"' . $list[WishlistStorage::NAME].'"'
                       . "\nМожно посмотреть его содержимое и подписаться на его обновления.";


                   return $this->textResponse($text)
                       ->addKeyboardKey('Посмотреть желания', $this->r(WList::class), ['lid' => $wlId, ] )
                       ;
               }
            }
        }

        $text = "Привет! \nЗдесь ты можешь создать свой вишлист и подписываться на вишлисты друзей!\n"
            . "Список доступных комманд\n";

        $response = $this->textResponse($text);
        $response
            ->addKeyboardKey('Добавить желание', $this->r(Draft::class))
            ->addKeyboardKey('Посмотреть все желания', $this->r(All::class))
            ->addKeyboardKey('Посмотреть свои вишлисты', $this->r(Wishlist::class))
            ->addKeyboardKey('Найти вишлист', $this->r(Search::class))
        ;

        return $response;
    }

}