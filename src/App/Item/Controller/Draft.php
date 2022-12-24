<?php


namespace App\Item\Controller;


use App\Done\Controller\Done;
use App\Item\Service\ItemStorage;
use App\Wishlist\Controller\Wishlist;
use App\Wishlist\Service\WishlistService;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Run\Util\Uuid;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Draft extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        // Делаем так, чтобы следующий текстовый ввод влетал в этот же контроллер
        $this->setNextResource($this->r(self::class));

        $text = $this->p('text');

        if (!$text) {
            return $this->textResponse('Назови своё желание:')
                ->addKeyboardKey('Назад', $this->getBrowseBackResource(), [], MessageRoute::APPEAR_CALLBACK_ANSWER);
        }

        if (mb_eregi('ничего|закончил|хватит', $text) !== false) {
            $this->setNextResource(null);
            return $this->textResponse("Хорошо, закончили");
        }

        //

        // Remove all illegal characters from a url
        $url = filter_var($text, FILTER_SANITIZE_URL);

        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            $ctx = stream_context_create(array(
                    'http' => array(
                        'timeout' => 1
                    )
                )
            );
            $page = file_get_contents($url, 0, $ctx);
            preg_match('/<title.*>(.*?)<\/title>/s', $page, $match);
            $title = strip_tags($match[1] ?? '');
            if ($title && mb_strlen($title) > 1) {
                $text = $title;
            }
        } else {
            $url = '';
        }

        // записываем желание
        $storage = new ItemStorage();
        $id = Uuid::v4();

        $writeResult = $storage->write()->insert($id, [
            ItemStorage::NAME => $text,
            ItemStorage::LINK => $url,
            ItemStorage::USER_ID => $this->getUserId(),
            ItemStorage::CREATED_AT => time(),
        ], __METHOD__);

        if (!$writeResult) {
            return $this->textResponse('Не удалось записать желание.');
        }

        // ищем текущий список
        $wlService = new WishlistService();
        $listData = [];

        $listId = $this->p('lid');
        if (!$listId) {
            $listId = $this->getState('lid');
            if (!$listId) {

                $listData = $wlService->createOrLoadUserWishlist($this->getUserId());
                $listId = $listData[WishlistStorage::ID];
            }
        }

        if (!$listData) {
            $listData = $wlService->getWishlistData($listId);
        }

        $items = array_merge($listData[WishlistStorage::ITEMS] ?? [], [$id]);

        $wlService->updateWishlist($listId, [
            WishlistStorage::ITEMS => $items
        ]);

        $text = "\"$text\" - Добавлено в \"{$listData[WishlistStorage::NAME]}\"\n";
        $text .= "Что еще хочешь?";

        return $this->textResponse($text)
            ->addKeyboardKey('Покажи все желания', $this->r(Wishlist::class), ['lid' => $listId])
            ->addKeyboardKey('Пока ничего', $this->r(Done::class), [], MessageRoute::APPEAR_CALLBACK_ANSWER)
            ->addKeyboardKey('Отмени добавление', $this->r(Delete::class), ['iid' => $id], MessageRoute::APPEAR_CALLBACK_ANSWER);
    }

}