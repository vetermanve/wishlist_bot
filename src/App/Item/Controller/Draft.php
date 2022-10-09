<?php


namespace App\Item\Controller;


use App\Done\Controller\Done;
use App\Item\Service\ItemStorage;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Draft extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource('/item_draft');

        $text = $this->p('text');

        if (!$text) {
            return $this->textResponse('Что ты хочешь? Напиши!')
                ->addKeyboardKey('Пока не хочу', '/done', [], MessageRoute::APPEAR_CALLBACK_ANSWER);
        }

        if (mb_eregi('ничего|закончил|хватит', $text) !== false) {
            $this->setNextResource(null);
            return $this->textResponse("Хорошо, закончили");
        }

        // записываем желание
        $storage = new ItemStorage();
        $id = Uuid::v4();

        $writeResult = $storage->write()->insert($id, [
            ItemStorage::NAME => $this->p('text'),
            ItemStorage::USER_ID => $this->getUserId(),
            ItemStorage::CREATED_AT => time(),
        ], __METHOD__);

        if (!$writeResult) {
            return $this->textResponse('Не удалось записать желание.');
        }

        // ищем текущий список
        $wlService = new WishlistService();
        $listData = [];

        $listId = $this->p('lid');
        if (!$listId) {
            $listId = $this->getState('lid');
            if (!$listId) {

                $listData = $wlService->createOrLoadUserWishlist($this->getUserId());
                $listId = $listData[WishlistUserStorage::WISHLIST_ID];
            }
        }

        if (!$listData) {
            $listData = $wlService->getWishlistData($listId);
        }

        $items = array_merge($listData[WishlistStorage::ITEMS] ?? [], [$id]);


        $wlService->updateWishlist($listId, [
            WishlistStorage::ITEMS => $items
        ]);

        $text = "Я записал, что ты хочешь: $text\n";
        $text .= "В список \"{$listData[WishlistStorage::NAME]}\"\n";
        $text .= "Что еще хочешь?";

        return $this->textResponse($text)
            ->addKeyboardKey('Отменить', $this->r(Delete::class), ['iid' => $id],MessageRoute::APPEAR_CALLBACK_ANSWER)
            ->addKeyboardKey('Показать все желания', $this->r(All::class), ['iid' => $id])
            ->addKeyboardKey('Давай закончим.', $this->r(Done::class), [], MessageRoute::APPEAR_CALLBACK_ANSWER);
    }

}