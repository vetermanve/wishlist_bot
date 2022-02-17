<?php

namespace App\Wishlist\Controller;

use App\Wishlist\Service\WishlistUserStorage;
use Psr\Log\LoggerInterface;
use Run\Controller\TelegramExtendedController;
use Verse\Di\Env;
use Verse\Run\RunContext;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Create extends TelegramExtendedController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();
        $storage = new WishlistUserStorage();
        $listData = $storage->read()->get($userId, __METHOD__);
        $listId = $listData[WishlistUserStorage::WISHLIST_ID] ?? null;

        if ($listId) {
            return $this->response()->setText('Твой вишлист: '.$listId)
                ->addKeyboardKey('Посмотреть', '/wishlist?id='.$listId);

        } else {
            $listId = Uuid::v4();
            $result = $storage->write()->insert($userId, [WishlistUserStorage::WISHLIST_ID => $listId],  __METHOD__);
        }

        return $this->textResponse("Твой вишлист создан: ". $listId . json_encode($result));
    }




}