<?php


namespace App\Item\Service;


use Verse\Storage\Spec\Compare;

class ItemService
{
    protected $itemStorage;

    private function getItemStorage(): ItemStorage
    {
        if (!$this->itemStorage) {
            $this->itemStorage = new ItemStorage();
        }

        return $this->itemStorage;
    }

    public function getAllItemsForUserByScan($userId) : ?array
    {
        $filters = [
            [ItemStorage::USER_ID, Compare::EQ, $this->getUserId()]
        ];

        $items = $this->getItemStorage()->search()->find($filters, 100, __METHOD__, [
            'sort' => [[ItemStorage::CREATED_AT, 'desc']],
        ]);

        return $items;
    }

    public function getItemsByIds(array $itemsIds)
    {
        return $this->getItemStorage()->read()->mGet($itemsIds, __METHOD__);
    }
}