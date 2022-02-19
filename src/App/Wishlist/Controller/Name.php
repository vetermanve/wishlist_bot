<?php

namespace App\Wishlist\Controller;

use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use Psr\Log\LoggerInterface;
use Run\Controller\TelegramExtendedController;
use Verse\Di\Env;
use Verse\Run\RunContext;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Name extends TelegramExtendedController {

    public function text_message(): ?TelegramResponse
    {
        $listId = $this->p('lid');
        if (!$listId) {
            return $this->textResponse("Я не пойму какой вишлист ты хочешь назвать!");
        }

        $storage = new WishlistStorage();
        $listData = $storage->read()->get($listId, __METHOD__);

        $bind = [
            WishlistStorage::NAME => $this->p('text')
        ];

//        if (!$listData) {
//            return $this->textResponse("Лист передан, но я не могу найти по нему данные!");
//        }

        $result = $storage->write()->update($listId, $bind, __METHOD__);

//        $listId = $listData[WishlistUserStorage::WISHLIST_ID] ?? null;

        if ($result) {
            return $this->response()->setText('Твой вишлист: '.json_encode($result))
                ->addKeyboardKey('Сменить название', '/wishlist_name',
                    [
                        'lid' => $listId,
                    ])
                ->addKeyboardKey('К списку вишлистов', '/wishlist')
                ;

        }

        return $this->textResponse("Что-то пошло не так и переименовать не удалось\n /done чтобы закончить именовать." );
    }

    public function callback_query(): ?TelegramResponse
    {
        $listId = $this->p('lid');
        if (!$listId) {
            return $this->textResponse("Не понятно какой вишлист будем именовать!");
        }

        $this->setNextResource('/wishlist_name', ['lid' => $listId]);
        return $this->textResponse("Напиши имя для вишлиста " . $listId);
    }


}