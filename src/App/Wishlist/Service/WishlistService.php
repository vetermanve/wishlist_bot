<?php


namespace App\Wishlist\Service;


use Verse\Run\Util\Uuid;

class WishlistService
{
    private string $defaultListName = 'Список желаний';

    public function getUserWishlistData($userId)
    {
        $listId = $this->getUserWishlistId($userId);
        return $this->getWishlistData($listId);
    }

    public function getUserWishlistId($userId)
    {
        $wishlistUserStorage = new WishlistUserStorage();
        $userListsData = $wishlistUserStorage->read()->get($userId, __METHOD__);
        return $userListsData[WishlistUserStorage::WISHLIST_ID] ?? null;
    }

    public function getWishlistData($listId)
    {
        if (!$listId) {
            return null;
        }

        $wishlistStorage = new WishlistStorage();
        return $wishlistStorage->read()->get($listId, __METHOD__);
    }

    public function assignWishlistToUser($userId)
    {
        $listId = Uuid::v4();
        $wishlistUserStorage = new WishlistUserStorage();
        $result = $wishlistUserStorage->write()->insert($userId, [WishlistUserStorage::WISHLIST_ID => $listId], __METHOD__);
        return $result ? $listId : null;
    }

    public function createOrLoadUserWishlist($userId, $name = null)
    {
        $listId = $this->getUserWishlistId($userId);
        if (!$listId) {
            $listId = $this->assignWishlistToUser($userId);
            if (!$listId) {
                throw new \Exception('Cannot assign wishlist id to user');
            }
        }

        $wishlistStorage = new WishlistStorage();
        $listData = $this->getWishlistData($listId);
        if (!$listData) {
            $name = $name ?? $this->getDefaultListName();
            $listBind = [WishlistStorage::NAME => $name];
            $listData = $wishlistStorage->write()->insert($listId, $listBind, __METHOD__);
            if (!$listData) {
                throw new \Exception('Cannot write wishlist data on creation');
            }
        }

        return $listData;
    }

    public function updateWishlist($listId, $updateBind)
    {
        $wishlistStorage = new WishlistStorage();
        return $wishlistStorage->write()->update($listId, $updateBind, __METHOD__);
    }

    /**
     * @return string
     */
    public
    function getDefaultListName(): string
    {
        return $this->defaultListName;
    }

    /**
     * @param string $defaultListName
     */
    public
    function setDefaultListName(string $defaultListName): void
    {
        $this->defaultListName = $defaultListName;
    }
}