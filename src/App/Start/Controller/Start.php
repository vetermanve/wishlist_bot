<?php

namespace App\Start\Controller;

use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Start extends TelegramRunController {

    public function text_message(): ?TelegramResponse
    {
        return $this->textResponse('Привет! Этот бот поможет тебе записать в каких коробках что лежит!');
    }

}