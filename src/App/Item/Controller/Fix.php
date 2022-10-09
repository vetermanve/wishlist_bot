<?php


namespace App\Item\Controller;


use App\Item\Service\ItemStorage;
use App\Wishlist\Service\WishlistStorage;
use App\Wishlist\Service\WishlistUserStorage;
use App\Base\Controller\WishlistBaseController;
use Verse\Storage\Spec\Compare;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Fix extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $userWishlistStorage = new WishlistUserStorage();
        $usersLists = $userWishlistStorage->search()->find([], 1000, __METHOD__);
        $listsIdsByUsers = array_column($usersLists, WishlistUserStorage::WISHLIST_ID, WishlistUserStorage::USER_ID);
        $users = array_keys($usersLists);

        $itemsStorage = new ItemStorage();
        $items = $itemsStorage->search()->find([
            [ItemStorage::USER_ID, Compare::IN, $users]
        ], 1000, __METHOD__);

        $wishlistItems = [];
        foreach ($items as $item) {
            $wlId = $listsIdsByUsers[$item[ItemStorage::USER_ID]];
            if (!isset($wishlistItems[$wlId])) {
                $wishlistItems[$wlId] = [
                    WishlistStorage::ITEMS => []
                ];
            }

            $wishlistItems[$wlId][WishlistStorage::ITEMS][] = $item[ItemStorage::ID];
        }

        $wishlistStorage = new WishlistStorage();
        $wishlistStorage->write()->updateBatch($wishlistItems, __METHOD__);

        return $this->textResponse('FIX'. json_encode($wishlistItems, JSON_PRETTY_PRINT));
    }

}