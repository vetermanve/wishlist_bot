<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Draft extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $text = $this->p('text');
        if (!$text) {
            $this->setNextResource('/item_draft');
            return $this->textResponse('Что ты хочешь? Напиши!')
                ->addKeyboardKey('Пока не хочу','/done', MessageRoute::APPEAR_CALLBACK_ANSWER);
        }

        $storage = new ItemStorage();
        $id = Uuid::v4();

        $storage->write()->insert($id, [
            ItemStorage::NAME => $this->p('text'),
            ItemStorage::USER_ID => $this->getUserId()
        ], __METHOD__);

        return $this->textResponse('Я записал, что ты хочешь: '.$this->p('text')
            ."\nЧто еще хочешь?"
        )
            ->addKeyboardKey('Отменить', '/item_delete', [ 'iid' => $id ],
                MessageRoute::APPEAR_CALLBACK_ANSWER
            )
            ->addKeyboardKey('Показать все желания', '/item_all', [ 'iid' => $id ])
            ->addKeyboardKey('Пока всё, ничего больше не хочу.', '/done', [], MessageRoute::APPEAR_CALLBACK_ANSWER)
            ;

    }

}