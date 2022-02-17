<?php

namespace App\Start\Controller;

use App\Wishlist\Service\WishlistStorage;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Start extends TelegramRunController {

    private static $service;

    private static $commands = [
        [
            'Cоздать вишлист',
            '/wishlist_new',
        ]
    ];

    public function text_message(): ?TelegramResponse
    {
        $response = $this->textResponse("Привет! \nЗдесь ты можешь создать свой вишлись и подписываться на вишлисты друзей!\n"
            . "Список доступных комманд\n" );

        foreach (self::$commands as $item) {
            [$desc, $command] = $item;
            $response->addKeyboardKey($desc, $command);
        }

        return $response;
    }


    /**
     * @return WishlistStorage
     */
    private function service() {
        if (!self::$service) {
            self::$service = new WishlistStorage();
        }
        return self::$service;
    }

}