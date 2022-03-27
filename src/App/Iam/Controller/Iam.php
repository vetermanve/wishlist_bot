<?php


namespace App\Iam\Controller;


use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Iam extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        return $this->textResponse('Твой ID '.$this->getUserId());
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}