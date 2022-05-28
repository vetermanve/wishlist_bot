<?php


namespace Verse\Notify\Storage;


use App\Wishlist\Service\WishlistStorage;
use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class NotifyConnectionsStorage extends SimpleStorage
{
    /**
     * Link code
     */
    const ID = 'id';

    /**
     * Ids of connected devices
     * @link ConnectionTypes
     */
    const TYPE = 'type';

    /**
     * string
     */
    const EXT_ID = 'ext_id';

    /**
     * string
     */
    const KEY = 'key';
    const USER_ID = 'uid';
    const CHANNEL_ID = 'ch_id';
    const SENDER = 'sndr';
    const IS_ACTIVE = 'is_a';
    const EXPIRE_AT = 'exp';


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
        $adapter->setResource('connections');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}