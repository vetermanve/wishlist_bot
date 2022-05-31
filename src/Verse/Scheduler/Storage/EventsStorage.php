<?php


namespace Verse\Scheduler\Storage;


use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class EventsStorage extends SimpleStorage
{
    const ID = 'id';
    const TIME = 'time';
    const TTL = 'ttl';
    const ROUTE = 'route';
    const DATA = 'data';
    const USER = 'user';


    public function loadConfig()
    {
        // TODO: Implement loadConfig() method.
    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $adapter = new JBaseDataAdapter();
        // set data location
        $adapter->setDataRoot(getcwd().'/data');
        // set database (folder) name
        $adapter->setDatabase('scheduler');
        // set table (folder) name
        $adapter->setResource('events');

        $this->getDiContainer()->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }
}