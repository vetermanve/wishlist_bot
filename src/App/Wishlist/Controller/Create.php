<?php

namespace App\Wishlist\Controller;

use App\Done\Controller\Done;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use Psr\Log\LoggerInterface;
use App\Base\Controller\WishlistBaseController;
use Verse\Di\Env;
use Verse\Run\RunContext;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Create extends WishlistBaseController {

    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();


        $text = "Почти готов!\nНапиши название для своего вишлиста:";
        $this->setNextResourceByClass(Name::class, ['lid' => $listId, ]);

        return $this->textResponse($text)
            ->addKeyboardKey('Потом продолжу', $this->r(Done::class),
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