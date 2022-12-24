<?php


namespace App\Wishlist\Service;


use Verse\Run\Util\Uuid;

/**
 * Class WishlistService
 * @see WishlistStorage
 * @see WishlistUserStorage
 *
 * @package App\Wishlist\Service
 */
class WishlistService
{
    private string $defaultListName = 'Список желаний';

    /**
     * @return WishlistUserStorage
     */
    protected function getWishlistUserStorage(): WishlistUserStorage
    {
        $wishlistUserStorage = new WishlistUserStorage();
        return $wishlistUserStorage;
    }

    public function getUserWishlistData($userId)
    {
        $listId = $this->getUserWishlistId($userId);
        return $listId ? $this->getWishlistData($listId) : null;
    }

    public function getUserWishlistId($userId)
    {
        $wishlistUserStorage = $this->getWishlistUserStorage();
        $userListsData = $wishlistUserStorage->read()->get($userId, __METHOD__);
        return $userListsData[WishlistUserStorage::DEFAULT_WISHLIST_ID] ?? null;
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
        $result = $this->getWishlistUserStorage()->write()->insert($userId, [WishlistUserStorage::DEFAULT_WISHLIST_ID => $listId], __METHOD__);
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

        $wishlistStorage = $this->getWishlistStorage();
        $listData = $this->getWishlistData($listId);
        if (!$listData) {
            $name = $name ?? $this->getDefaultListName();

            $listBind = [
                WishlistStorage::NAME => $name,
                WishlistStorage::OWNER => $userId
            ];

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

    public function getAllUserWishlists($userId)
    {
        $data = $this->getUserWishlistData($userId);
        return $data ? [$data[WishlistStorage::ID] => $data] : [];
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

    public function removeItemFromAllWishlists($userId, $itemId)
    {
        $listData = $this->getUserWishlistData($userId);
        if (!$listData) {
            throw new \Exception('Default list not found!');
        }

        $items = $listData[WishlistStorage::ITEMS];

        $key = array_search($itemId, $items);

        if ($key !== false) {
            unset($items[$key]);
            $this->getWishlistStorage()->write()->update($listData[WishlistStorage::ID], [WishlistStorage::ITEMS => array_values($items)], __METHOD__);
        }
    }

    /**
     * @return WishlistStorage
     */
    protected function getWishlistStorage(): WishlistStorage
    {
        $wishlistStorage = new WishlistStorage();
        return $wishlistStorage;
    }


}