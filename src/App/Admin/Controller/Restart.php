<?php


namespace App\Admin\Controller;


use Telegram\Bot\Objects\BotCommand;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Service\VerseTelegramClient;

class Restart extends WishlistBaseController
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