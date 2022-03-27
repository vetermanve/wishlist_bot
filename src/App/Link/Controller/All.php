<?php


namespace App\Link\Controller;


use App\Link\Service\Links;
use App\Link\Service\LinkStorage;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Controller\TelegramResponse;

class All extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $id = $this->p('lid');
        if (!$id) {
            return $this->textResponse('Какая-то ошибка, не могу найти идентификатор вишлиста.');
        }

        $storage = new LinkStorage();
        $links = $storage->search()->find([
            [LinkStorage::WL_ID, Compare::EQ, $id]
        ], 100, __METHOD__);

        $response = null;
        if (!$links) {
            $response = $this->textResponse('На этот вишлист пока нет ссылок');
        } else {
            $text = '';
            foreach ($links as $item) {
                $text.= "\n - ".Links::getLink($item[LinkStorage::ID]);
            }
            $response = $this->textResponse("Аткивные ссылки:\n".$text);
        }

        return $response
            ->addKeyboardKey('Добавить случайную ссылку', $this->r(Create::class), [
                'lid' => $id,
            ])
        ;
    }

}