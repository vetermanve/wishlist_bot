<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Start\Controller\Start;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Delete extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $id = $this->p('iid');
        if (!$id) {
            return $this->textResponse('Не знаю что удалить');
        }

        // удаляем из всех списков
        $wishlistService  = new WishlistService();
        $wishlistService->removeItemFromAllWishlists($this->getUserId(), $id);

        // удаляем саму запись
        $itemStorage = new ItemStorage();
        $result = $itemStorage->write()->remove($id,__METHOD__);
        if (!$result) {
            return $this->textResponse('Не удалось удалить запись.');
        }

        return $this->textResponse('Желание удалено')
            ->addKeyboardKey('Вернуться назад', $this->getBrowseBackResource())
            ->addKeyboardKey('В начало',   $this->r(Start::class))
        ;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}