<?php


namespace App\Done\Controller;


use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Done extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null, null);
        return $this->textResponse('Текущая задача завершена');
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}