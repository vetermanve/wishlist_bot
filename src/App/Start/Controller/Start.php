<?php

namespace App\Start\Controller;

use App\Item\Controller\AllItems;
use App\Item\Controller\Draft;
use App\Item\Controller\WList;
use App\Link\Service\LinkStorage;
use App\Wishlist\Controller\Search;
use App\Wishlist\Controller\Wishlist;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Start extends WishlistBaseController
{

    public function text_message(): ?TelegramResponse
    {
        $text = trim($this->p('text'));
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

        $name = $this->p('from')['first_name'] ?? '';
//        $text = "Привет! \nЗдесь ты можешь";

        $wishlistService = new WishlistService();
        $list = $wishlistService->getUserWishlistData($this->getUserId());

        $this->rememberBrowseBackResource();
        $this->setNextResource(null);

        $text = $this->_render('start', [
            'name' => $name,
            'list' => $list,
        ]);

        $response = $this->textResponse($text);
        $response
            ->addKeyboardKey('Добавить желание', $this->r(Draft::class))
            ->addKeyboardKey('Перейти в свой список желаний', $this->r(Wishlist::class), [],MessageRoute::APPEAR_NEW_MESSAGE)
            ->addKeyboardKey('Поделиться списком желаний', $this->r(Search::class))
            ->addKeyboardKey('Найти список желаний другого человека', $this->r(Search::class))
        ;

        return $response;
    }

}