<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class AllItems extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $this->rememberBrowseBackResource();
        $this->setNextResource(null);

        $storage = new ItemStorage();

        $filters = [
            [ItemStorage::USER_ID, Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100, __METHOD__, [
            'sort' => [[ItemStorage::CREATED_AT, 'desc']],
        ]);

        $this->setState('edit_mode', false);

        $text = "ТУТ ТЕПЕРЬ НИЧЕЕГО НЕТ";

        return $this->textResponse($text)
            ->addKeyboardKey('Добавить желание', $this->r(Draft::class), [])
            ->addKeyboardKey('Управлять желаниями', $this->r(EditMode::class), [], MessageRoute::APPEAR_EDIT_MESSAGE)
            ->addKeyboardKey('Обновить список', $this->r(AllItems::class), [], MessageRoute::APPEAR_EDIT_MESSAGE);
    }

}