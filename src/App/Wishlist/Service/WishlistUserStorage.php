<?php


namespace App\Wishlist\Service;


use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleJsonStorageTest;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class WishlistUserStorage extends SimpleStorage
{
    const WISHLIST_ID = 'wl_id';

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
        $adapter->setResource('user_wishlist');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}