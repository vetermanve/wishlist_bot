<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class EditMode extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $selectedId = $this->p('text');
        if (!is_numeric($selectedId)) {
            $selectedId = null;
        } else {
            $selectedId = intval($selectedId);
        }

        $storage = new ItemStorage();

        $filters = [
            [ItemStorage::USER_ID,  Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100,  __METHOD__,[
            'sort' => [[ItemStorage::CREATED_AT, 'desc']],
        ]);

        if ($selectedId && $selectedId-1 <= sizeof($items)) {
            return $this->_renderSelectedItem($selectedId, $items);
        }

        $text = 'Выбери желание для редактирования: ';

        $id = 0;
        foreach ($items as $item) {
            $id++;
            $text.= "\n/$id ".$item[ItemStorage::NAME];
        }

        $this->setNextResourceByClass(EditMode::class);

        $this->setState('edit_mode', 1);

        return $this->textResponse($text)
            ->addKeyboardKey('Закончить редактирование', $this->getResourceByClass(All::class), [], MessageRoute::APPEAR_EDIT_MESSAGE)
            ->addKeyboardKey('Обновить', $this->getResourceByClass(EditMode::class), [], MessageRoute::APPEAR_EDIT_MESSAGE)
            ;
    }

    private function _renderSelectedItem(?int $selectedId, $items)
    {
        $items = array_values($items);
        $item = $items[$selectedId-1];
        return $this->textResponse('Хочу: '.$item[ItemStorage::NAME])
            ->addKeyboardKey('Редактировать', $this->getResourceByClass(Edit::class), ['iid' => $item[ItemStorage::ID]])
            ->upendKeyboardKey('Удалить', $this->getResourceByClass(Delete::class), ['iid' => $item[ItemStorage::ID]])
            ;
    }

}