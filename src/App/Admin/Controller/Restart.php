<?php


namespace App\Admin\Controller;


use Telegram\Bot\Objects\BotCommand;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Service\VerseTelegramClient;

class Restart extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        exit(1);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}