<?php

namespace App\Wishlist\Controller;

use App\Item\Controller\All;
use App\Item\Controller\Draft;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Wishlist extends WishlistBaseController
{

    public static string $description = 'Все вишлисты';

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();

        $service = new WishlistService();
        $list = $service->createOrLoadUserWishlist($userId);
        $listId = $list[WishlistStorage::ID];

        if ($list) {
            $text = "Вишлист \"" . $list[WishlistStorage::NAME] . '"';
        }

        return $this->textResponse($text)
            ->addKeyboardKey("Добавить желание", $this->r(Draft::class), ['lid' => $listId,])
            ->addKeyboardKey("Посмотреть желания", $this->r(All::class), ['lid' => $listId,])
            ->addKeyboardKey("Переименовать", $this->r(Name::class), ['lid' => $listId,])
            ->addKeyboardKey("Поделиться, Управлять ссылками.", $this->r(\App\Link\Controller\All::class), ['lid' => $listId,]);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}