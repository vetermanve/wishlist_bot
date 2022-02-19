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
        $storage = new ItemStorage();
        $id = Uuid::v4();

        $filters = [
            [ItemStorage::USER_ID,  Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100,  __METHOD__);

        $text = 'Твои желания: ';
        foreach ($items as $id => $item) {
            $text.= "\n - ".$item[ItemStorage::NAME];
        }

        return $this->textResponse($text);
    }

}