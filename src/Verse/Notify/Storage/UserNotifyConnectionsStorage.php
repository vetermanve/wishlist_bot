<?php


namespace Verse\Notify\Storage;


use App\Wishlist\Service\WishlistStorage;
use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class UserNotifyConnectionsStorage extends SimpleStorage
{
    /**
     * Link code
     */
    const USER_ID = 'id';

    /**
     * Ids of connected devices
     * @link WishlistStorage
     */
    const CONNECTIONS = 'cons';

    public function loadConfig()
    {

    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $adapter = new JBaseDataAdapter();
        // set data location
        $adapter->setDataRoot(getcwd().'/data');
        // set database (folder) name
        $adapter->setDatabase('run_notify');
        // set table (folder) name
        $adapter->setResource('users_to_connections');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}