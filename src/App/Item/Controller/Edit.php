<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use Run\Controller\TelegramExtendedController;
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

        if(isset($field)) {
            if (!$text) {
                $this->setNextResource($classResource, ['f' => $field]);
                switch ($field) {
                    case ItemStorage::NAME:
                        $text = "Текущий заголовок \"{$item[ItemStorage::NAME]}\"\nВведи новый:";
                        break;
                    case ItemStorage::PRICE:
                        $text = "Текущая цена \"{$item[ItemStorage::PRICE]}\"\nВведи новую:";
                        $this->setNextResource($classResource);
                        break;
                    case ItemStorage::LINK:
                        $text = "Текущая ссылка \"{$item[ItemStorage::PRICE]}\"\nВведи новую:";
                        break;
                }

                return $this->textResponse($text);
            } else {
                $result = $storage->write()->update($itemId, [
                    $field => $text
                ], __METHOD__);

                var_dump($result);

                if($result) {
                    $this->setNextResource(null);

                    return $this->textResponse("Записано! ".self::$allowedFields[$field].' = '. $text);
                } else {
                    return $this->textResponse("Не получилось записать =( ");
                }
            }
        }

        return $this->textResponse('Что ты хочешь отредактировать?')
            ->addKeyboardKey('Название', $classResource, ['iid' => $itemId, 'f' => ItemStorage::NAME])
            ->addKeyboardKey('Цену', $classResource, ['iid' => $itemId, 'f' => ItemStorage::NAME])
            ->addKeyboardKey('Ссылку', $classResource, ['iid' => $itemId, 'f' => ItemStorage::NAME]);
    }

}