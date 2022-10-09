<?php


namespace App\Landing\Controller;


use App\Item\Controller\All;
use App\Wishlist\Controller\Wishlist;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Landing extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);
        $text = mb_strtolower($this->p('text'));

        if (strpos($text, 'привет') !== false) {
            return $this->textResponse('И тебе привет '.($this->p('from')['first_name'] ?? '').'!');
        }

        return $this->textResponse('Не понял команды "' . $this->p('text').'"')
            ->addKeyboardKey('Все вишлисты', $this->getResourceByClass(Wishlist::class))
            ->addKeyboardKey('Все желания', $this->getResourceByClass(All::class))
            ;
    }
}