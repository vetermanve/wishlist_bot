<?php

namespace App\Wishlist\Controller;

use App\Item\Controller\AllItems;
use App\Item\Controller\Draft;
use App\Item\Controller\Edit;
use App\Item\Service\ItemService;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Wishlist extends WishlistBaseController
{

    public static string $description = 'Все желания';

    public function text_message(): ?TelegramResponse
    {
        $listId = $this->p('lid');
        $userId = $this->getUserId();

        $this->rememberBrowseBackResource();
        $this->setNextResource($this->r(Edit::class));

        $service = new WishlistService();

        if ($listId) {
            $list = $service->getWishlistData($listId);
            if (!$list) {
                return $this->textResponse('Список не найден');
            }
        } else {
            $list = $service->createOrLoadUserWishlist($userId);
            $listId = $list[WishlistStorage::ID];
        }

        if ($list[WishlistStorage::OWNER] !== $userId) {
            return $this->textResponse('Список принадлежит другому пользователю, вы не можете его просмотреть.');
        }

        // load items
        $itemsService = new ItemService();
        $items = !empty($list[WishlistStorage::ITEMS])
            ? $itemsService->getItemsByIds($list[WishlistStorage::ITEMS])
            : [];

        //render
        $text = $this->_render('list_items', [
            'list' => $list,
            'items' => $items
        ]);

        return $this->textResponse($text)
            ->addKeyboardKey("Добавить желание", $this->r(Draft::class), ['lid' => $listId,])
            ->addKeyboardKey("Переименовать список", $this->r(Name::class), ['lid' => $listId,])
            ->addKeyboardKey("Поделиться", $this->r(\App\Link\Controller\All::class), ['lid' => $listId,])
            ->addKeyboardKey("Скрыть", $this->r(\App\Link\Controller\All::class), ['lid' => $listId,]);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}