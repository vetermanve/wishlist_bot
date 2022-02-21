<?php

namespace App\Wishlist\Controller;

use App\Item\Controller\Draft;
use App\Wishlist\Service\WishlistStorage;
use Exception;
use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Name extends TelegramExtendedController {

    /**
     * @return TelegramResponse|null
     * @throws Exception
     */
    public function text_message(): ?TelegramResponse
    {
        $listId = $this->p('lid');
        if (!$listId) {
            return $this->textResponse("Я не пойму какой вишлист ты хочешь назвать!");
        }

        $storage = new WishlistStorage();
//        $listData = $storage->read()->get($listId, __METHOD__);

        $bind = [
            WishlistStorage::NAME => $this->p('text')
        ];

        $result = $storage->write()->update($listId, $bind, __METHOD__);

        if ($result) {
            $this->setNextResource(null);
            return $this->textResponse('Записал: "' .$bind[WishlistStorage::NAME].'"')
                ->addKeyboardKey('Изменить название', $this->r(Name::class), [ 'lid' => $listId,])
                ->addKeyboardKey('Перейти к вишлисту', $this->r(Wishlist::class))
                ->addKeyboardKey('Добавить желание', $this->r(Draft::class))
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
        return $this->textResponse("Придумай и напиши новое название для вишлиста");
    }


}