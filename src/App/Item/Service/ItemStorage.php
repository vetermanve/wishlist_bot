<?php


namespace App\Item\Service;


use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleJsonStorageTest;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class ItemStorage extends SimpleStorage
{
    const ID = 'id';
    const NAME = "name";
    const PRICE = "price";
    const LINK = "link";
    const CREATED_AT = "created_at";
    const USER_ID = "uid";

    public function loadConfig()
    {

    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $adapter = new JBaseDataAdapter();
        // set data location
        $adapter->setDataRoot(getcwd().'/data');
        // set database (folder) name
        $adapter->setDatabase('we_wishlist');
        // set table (folder) name
        $adapter->setResource('item');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}