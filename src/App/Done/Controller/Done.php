<?php


namespace App\Done\Controller;


use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Done extends WishlistBaseController
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