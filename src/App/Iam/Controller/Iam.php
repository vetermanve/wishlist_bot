<?php


namespace App\Iam\Controller;


use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Iam extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        return $this->textResponse($this->_render('iam',['id' => $this->getUserId(), ]));
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}