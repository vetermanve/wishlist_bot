<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Delete extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $id = $this->p('iid');
        if (!$id) {
            return $this->textResponse('Не знаю что удалить');
        }

        $storage = new ItemStorage();

        $storage->write()->remove($id,__METHOD__);

        return $this->textResponse('Запись удалена');
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}