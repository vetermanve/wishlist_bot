<?php


namespace Verse\Scheduler\Scheme;


use Verse\Run\Component\CreateDependencyContainer;
use Verse\Run\Component\UnexpectedShutdownHandler;
use Verse\Run\Processor\SimpleRestProcessor;
use Verse\Run\Schema\PreconfiguredSchemaProto;
use Verse\Scheduler\Channel\StdoutDataChannel;
use Verse\Scheduler\Provider\StorageTimeEventsProvider;
use Verse\Telegram\Run\Channel\TelegramReplyChannel;
use Verse\Telegram\Run\Component\SetupTelegramNotifyGate;

class TimeStorageSchedulerScheme extends PreconfiguredSchemaProto
{
    public function configure()
    {
        // starting to catch errors
        $this->core->addComponent(new UnexpectedShutdownHandler());
        // booting dependency container
        $this->core->addComponent(new CreateDependencyContainer());

        // creating request provider
        $provider = new StorageTimeEventsProvider();
        $loopData = new \stdClass();
        $loopData->startTime = time();
        $loopData->workTime = 60*5; //restart gracefully every 5 minutes

        // expecting it to sto processing using this function
        $provider->setShouldProceedCallback(function () use ($loopData) {
            return time() - $loopData->startTime < $loopData->workTime;
        });

        $telegramChannel = new TelegramReplyChannel();

        // configure notification gate bootstrap
        $notificationGateLoader = new SetupTelegramNotifyGate();
        $notificationGateLoader->setTelegramChannel($telegramChannel);

        // add component to be booted
        $this->core->addComponent($notificationGateLoader);

        //bind processor to scheme
        $this->processor = new SimpleRestProcessor();

        $this->_addCustomComponents();

        $this->core->setProvider($provider);
        $this->core->setProcessor($this->processor);
        $this->core->setDataChannel(new StdoutDataChannel());
    }
}