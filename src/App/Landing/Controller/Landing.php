<?php


namespace App\Landing\Controller;


use Service\Emoji\CachedEmoji;
use Spatie\Emoji\Emoji;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Landing extends TelegramRunController
{
    public function text_message(): ?TelegramResponse
    {
        return $this->textResponse(implode('', CachedEmoji::getRandomEmoji(4)));
    }
}