<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Wishlist\Service\WishlistStorage;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class WList extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);
        $wlId = $this->p('lid');

        $listStorage = new WishlistStorage();
        $list = $listStorage->read()->get($wlId, __METHOD__);

        if (!$list) {
            return $this->textResponse('Список не найден!');
        }

        $itemsIds = $list[WishlistStorage::ITEMS];

        $storage = new ItemStorage();


        $items = $storage->read()->mGet($itemsIds, __METHOD__);

        $text = $list[WishlistStorage::NAME]."\n";

        foreach ($items as $item) {
            $text.= "\n - ".$item[ItemStorage::NAME];
        }

//        $this->setState('edit_mode', false);

        return $this->textResponse($text)
//            ->addKeyboardKey('Добавить желание', $this->r(Draft::class), [])
//            ->addKeyboardKey('Управлять желаниями', $this->r(EditMode::class), [],MessageRoute::APPEAR_EDIT_MESSAGE)
            ->addKeyboardKey('Обновить список', $this->r(self::class),  ['lid' => $wlId, ],MessageRoute::APPEAR_EDIT_MESSAGE)
            ;
    }

}