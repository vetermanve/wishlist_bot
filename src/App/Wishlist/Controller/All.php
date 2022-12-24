<?php

namespace App\Wishlist\Controller;

use App\Item\Controller\AllItems as AllItens;
use App\Item\Controller\Draft;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class All extends WishlistBaseController
{

    public static string $description = 'Все вишлисты';

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();

        $service = new WishlistService();
        $lists = $service->getAllUserWishlists($userId);

        $text = $this->_render('all', ['lists' => $lists]);

        $resp = $this->textResponse($text);

        foreach ($lists as $index => $list) {
            $resp ->addKeyboardKey("-> ".$list[WishlistStorage::NAME], $this->r(Wishlist::class), ['lid' => $list[WishlistStorage::ID],]);
        }

        return $resp;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}