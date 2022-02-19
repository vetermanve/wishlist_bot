<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class All extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);

        $storage = new ItemStorage();

        $filters = [
            [ItemStorage::USER_ID,  Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100,  __METHOD__,[
            'sort' => [[ItemStorage::CREATED_AT, 'desc']],
        ]);

        $text = 'Твои желания: '.date('d.M h:i');

        foreach ($items as $item) {
            $text.= "\n - ".$item[ItemStorage::NAME];
        }

        return $this->textResponse($text)
            ->addKeyboardKey('Управлять желаниями', $this->getResourceByClass(EditMode::class), [],MessageRoute::APPEAR_EDIT_MESSAGE)
            ->addKeyboardKey('Обновить', $this->getResourceByClass(All::class),  [],MessageRoute::APPEAR_EDIT_MESSAGE)
        ;
    }

}