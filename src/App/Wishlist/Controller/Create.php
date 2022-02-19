<?php

namespace App\Wishlist\Controller;

use App\Done\Controller\Done;
use App\Wishlist\Service\WishlistStorage;
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
        $wishlistUserStorage = new WishlistUserStorage();
        $wishlistStorage = new WishlistStorage();
        $userListsData = $wishlistUserStorage->read()->get($userId, __METHOD__);
        $listId = $userListsData[WishlistUserStorage::WISHLIST_ID] ?? null;

        if ($listId) {
            $listData = $wishlistStorage->read()->get($listId, __METHOD__);
            if($listData && isset($listData[WishlistStorage::NAME]) && $listData[WishlistStorage::NAME] !== '') {
                $text = 'Твой вишлист: '.$listData[WishlistStorage::NAME];
                return $this->textResponse($text)
                    ->addKeyboardKey('Переименовать', '/wishlist_name',
                        [
                            'lid' => $listId,
                        ])
                    ;
            }
        }

        $listId = $listId ?? Uuid::v4();
        $result = $wishlistUserStorage->write()->update($userId, [WishlistUserStorage::WISHLIST_ID => $listId],  __METHOD__);
        $listData = $wishlistStorage->write()->insert($listId,[], __METHOD__);

        $text = "Почти готов!\nНапиши название для своего вишлиста:";
        $this->setNextResourceByClass(Name::class, ['lid' => $listId, ]);

        return $this->textResponse($text)
            ->addKeyboardKey('Потом продолжу', $this->getResourceByClass(Done::class),
                [
                    'lid' => $listId,
                ])
            ;
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }


}