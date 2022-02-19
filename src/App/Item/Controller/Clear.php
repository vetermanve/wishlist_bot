<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Clear extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {

        $storage = new ItemStorage();

        $filters = [
            [ItemStorage::USER_ID,  Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100,  __METHOD__);
        $ids = array_column($items, 'id');

        $storage->write()->removeBatch($ids, __METHOD__);

        return $this->textResponse("Removed: \n:".implode("\n", array_column($items, ItemStorage::NAME)));

//
//
//        $this->setNextResource('/item_draft');
//
//        $text = $this->p('text');
//
//        if (!$text) {
//            return $this->textResponse('Что ты хочешь? Напиши!')
//                ->addKeyboardKey('Пока не хочу','/done', MessageRoute::APPEAR_CALLBACK_ANSWER);
//        }
//
//
//        if (mb_eregi('ничего|закончил|хватит', $text) !== false) {
//            $this->setNextResource(null);
//            return $this->textResponse("Хорошо, закончили");
//        }
//
//        $storage = new ItemStorage();
//        $id = Uuid::v4();
//
//        $storage->write()->insert($id, [
//            ItemStorage::NAME => $this->p('text'),
//            ItemStorage::USER_ID => $this->getUserId()
//        ], __METHOD__);
//
//        return $this->textResponse('Я записал, что ты хочешь: '.$this->p('text')
//            ."\nЧто еще хочешь?"
//        )
//            ->addKeyboardKey('Отменить', '/item_delete', [ 'iid' => $id ],
//                MessageRoute::APPEAR_CALLBACK_ANSWER
//            )
//            ->addKeyboardKey('Показать все желания', '/item_all', [ 'iid' => $id ])
//            ->addKeyboardKey('Давай закончим.', '/done', [], MessageRoute::APPEAR_CALLBACK_ANSWER)
//            ;

    }

}