<?php


namespace App\Admin\Controller;


use App\Wishlist\Controller\Wishlist;
use Telegram\Bot\Objects\BotCommand;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Service\VerseTelegramClient;

class SetCommands extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $client = new VerseTelegramClient();
        $client->getApi()->setMyCommands(
            ['commands' => [
                    new BotCommand(['command' => '/start', 'description' => 'В начало',]),
                    new BotCommand(['command' => $this->r(Wishlist::class), 'description' => Wishlist::$description,]),
                    new BotCommand(['command' => $this->r(Restart::class), 'description' => 'Перезапустить бота',]),
                    new BotCommand(['command' => $this->r(SetCommands::class), 'description' => 'Установит команды бота',]),
                ]
            ]
        );
        return $this->textResponse('Список команд отправлен');
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}