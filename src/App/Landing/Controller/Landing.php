<?php


namespace App\Landing\Controller;


use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Landing extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);
        $text = mb_strtolower($this->p('text'));

        if (strpos($text, 'привет') !== false) {
            return $this->textResponse('И тебе привет '.($this->p('from')['first_name'] ?? '').'!');
        }

        return $this->textResponse('Не понял команды "' . $this->p('text').'"');
    }
}