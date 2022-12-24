<?php


namespace App\Wishlist\Service;


use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleJsonStorageTest;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class WishlistStorage extends SimpleStorage
{
    const ID = "id";
    const NAME = "n";
    const OWNER = "owner";
    const ITEMS = "items";
    const LINKS = "links";
    const EDITORS = "editors";

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
        $adapter->setResource('wishlist');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}