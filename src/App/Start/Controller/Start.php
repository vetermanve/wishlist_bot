<?php

namespace App\Start\Controller;

use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class Start extends TelegramRunController {

    public function text_message(): ?TelegramResponse
    {
        return $this->textResponse("Привет! \nЗдесь ты можешь погадать на эмоджи. \nНапиши вопрос!");
    }

}