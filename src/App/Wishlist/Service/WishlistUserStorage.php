<?php


namespace App\Wishlist\Service;


use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleJsonStorageTest;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class WishlistUserStorage extends SimpleStorage
{
    const USER_ID = 'id';
    const DEFAULT_WISHLIST_ID = 'wl_id';
    const ALL_WISHLIST_IDS = 'wl_ids_all';
    const PUBLIC_WISHLIST_IDS = 'wl_ids_pub';
    const ARCHIVED_WISHLIST_IDS = 'wl_ids_arch';

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