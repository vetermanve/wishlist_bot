<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
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

        $itemStorage = new ItemStorage();
        $result = $itemStorage->write()->remove($id,__METHOD__);
        if (!$result) {
            return $this->textResponse('Не удалось удалить запись.');
        }

        $text = 'Запись удалена из списка всех желаний';

        $userWishlistStorage = new WishlistUserStorage();
        $userListData = $userWishlistStorage->read()->get($this->getUserId(),__METHOD__);

        if ($userListData) {
            $listId = $userListData[WishlistUserStorage::WISHLIST_ID];
            $wishlistStorage = new WishlistStorage();

            $listData = $wishlistStorage->read()->get($listId, __METHOD__);

            if ($listData) {
                $items = $listData[WishlistStorage::ITEMS];
                $key = array_search($id, $items);

                if ($key !== false) {
                    unset($items[$key]);
                    $wishlistStorage->write()->update($listId, [WishlistStorage::ITEMS => array_values($items)], __METHOD__);
                    $text .= ' и из списка "'.$listData[WishlistStorage::NAME].'"';
                }
            }
        }

        return $this->textResponse($text)
            ->addKeyboardKey('Вернуться к списку', '!list')
        ;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}