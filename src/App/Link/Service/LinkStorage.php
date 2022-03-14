<?php


namespace App\Link\Service;


use App\Wishlist\Service\WishlistStorage;
use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class LinkStorage extends SimpleStorage
{
    /**
     * Link code
     */
    const ID = 'id';

    /**
     * Wishlist ID
     * @link WishlistStorage
     */
    const WL_ID = 'wl_id';

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
        $adapter->setResource('links_to_wishlist');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}