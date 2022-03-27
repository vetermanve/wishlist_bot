<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Wishlist\Controller\Wishlist;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Run\Util\Uuid;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Edit extends TelegramExtendedController
{
    private static $allowedFields = [
        ItemStorage::NAME => "Заголовок",
        ItemStorage::PRICE => "Цена",
        ItemStorage::LINK => "Ссылка",
    ];

    public function text_message(): ?TelegramResponse
    {
        $itemId = $this->p('iid');
        $storage = new ItemStorage();
        $item = $storage->read()->get($itemId, __METHOD__);
        if (!$item) {
            $this->setNextResource(null);
            return $this->textResponse("Запись не найдена!");
        }

        $classResource = $this->getResourceByClass(Edit::class);
        $this->setNextResource($classResource);

        $text = $this->p('text');
        $field = $this->p('f', $this->getState('f'));
        if (!isset(self::$allowedFields[$field])) {
            $field = null;
        }

        $editOptions = [
            ItemStorage::NAME => 'Название',
            ItemStorage::PRICE => 'Цену',
            ItemStorage::LINK => 'Ссылку'
        ];

        if(isset($field)) {
            if (!$text) {
                $this->setNextResource($classResource, [
                    'f' => $field,
                    'iid' => $itemId,
                ]);
                switch ($field) {
                    case ItemStorage::NAME:
                        $text = "Текущий заголовок \"{$item[ItemStorage::NAME]}\"\nВведи новый:";
                        break;
                    case ItemStorage::PRICE:
                        $text = "Текущая цена \"{$item[ItemStorage::PRICE]}\"\nВведи новую:";
                        break;
                    case ItemStorage::LINK:
                        $text = "Текущая ссылка \"{$item[ItemStorage::LINK]}\"\nВведи новую:";
                        break;
                }

                return $this->textResponse($text);
            } else {
                $result = $storage->write()->update($itemId, [
                    $field => $text
                ], __METHOD__);

                if($result) {
                    $this->setNextResource(null);

                    $response = $this->textResponse("Записано! ".self::$allowedFields[$field].' = '. $text
                        . "\nИзменить еще:"
                    );

                    unset($editOptions[$field]);

                    foreach ($editOptions as $field => $text) {
                        $response->addKeyboardKey($text, $classResource, ['iid' => $itemId, 'f' => $field]);
                    }

                    return $response
                        ->addKeyboardKey('Вернуться к списку', '!list')
                    ;

                } else {
                    return $this->textResponse("Не получилось записать =( ");
                }
            }
        }

        $response = $this->textResponse('Что ты хочешь отредактировать?');

        foreach ($editOptions as $field => $text) {
            $response->addKeyboardKey($text, $classResource, ['iid' => $itemId, 'f' => $field]);
        }

        return $response;
    }

}