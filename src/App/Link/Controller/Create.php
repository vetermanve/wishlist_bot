<?php


namespace App\Link\Controller;


use App\Link\Service\Links;
use App\Link\Service\LinkStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Create extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $wlId = $this->p('lid');

        if (!$wlId) {
            return $this->textResponse('Какая-то ошибка, не могу найти идентификатор вишлиста.');
        }

        $tries = 10;
        do {
            $id = bin2hex(random_bytes(16));
            $storage = new LinkStorage();
            $result = $storage->write()->insert($id, [LinkStorage::WL_ID => $wlId], __METHOD__);
        } while (!$result && --$tries > 0);

        if (!$result) {
            return $this->textResponse("Не удалось сгенерировать ссылку");
        }

        return $this->textResponse("Ссылка создана " . Links::getLink($id));
    }

}