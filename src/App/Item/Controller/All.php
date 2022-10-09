<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class All extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);

        $storage = new ItemStorage();

        $filters = [
            [ItemStorage::USER_ID, Compare::EQ, $this->getUserId()]
        ];

        $items = $storage->search()->find($filters, 100, __METHOD__, [
            'sort' => [[ItemStorage::CREATED_AT, 'desc']],
        ]);

        $this->setState('edit_mode', false);

        $text = $this->_render('all',['items' => $items]);

        return $this->textResponse($text)
            ->addKeyboardKey('Добавить желание', $this->r(Draft::class), [])
            ->addKeyboardKey('Управлять желаниями', $this->r(EditMode::class), [], MessageRoute::APPEAR_EDIT_MESSAGE)
            ->addKeyboardKey('Обновить список', $this->r(All::class), [], MessageRoute::APPEAR_EDIT_MESSAGE);
    }

}