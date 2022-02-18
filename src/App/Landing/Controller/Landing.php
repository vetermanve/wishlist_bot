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
        $text = mb_strtolower($this->p('text'));

        if (strpos($text, 'привет') !== false) {
            return $this->textResponse('И тебе привет '.($this->p('from')['first_name'] ?? '').'!');
        }

        return $this->textResponse('Не понял команды "' . $this->p('text').'"');
    }
}