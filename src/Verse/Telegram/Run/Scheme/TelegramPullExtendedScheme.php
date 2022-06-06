<?php


namespace Verse\Telegram\Run\Scheme;


use Verse\Run\Component\CreateDependencyContainer;
use Verse\Run\Component\UnexpectedShutdownHandler;
use Verse\Telegram\Run\Channel\SolidStateTelegramResponseChannel;
use Verse\Telegram\Run\Channel\TelegramReplyChannel;
use Verse\Telegram\Run\Component\RuntimeLoggerBinder;
use Verse\Telegram\Run\Component\SetupTelegramNotifyGate;
use Verse\Telegram\Run\RequestRouter\StateBasedRequestRouter;
use Verse\Telegram\Run\Storage\UserStateStorage;
use Service\Routing\TextRouting;
use Verse\Telegram\Run\Processor\TelegramUpdateProcessor;
use Verse\Telegram\Run\Scheme\TelegramPullScheme;

class TelegramPullExtendedScheme extends TelegramPullScheme
{
    public function configure()
    {
        // configure state storage
        $stateStorage = new UserStateStorage();

        // configure telegram channel
        $channel = new SolidStateTelegramResponseChannel();
        $channel->setStateStorage($stateStorage);

        // bind telegram channel to core
        $this->core->setDataChannel($channel);

        // configure notification gate bootstrap
        $notificationGateLoader = new SetupTelegramNotifyGate();
        $notificationGateLoader->setTelegramChannel($channel);

        // add component to be booted
        $this->addComponent($notificationGateLoader);
        $this->addComponent(new RuntimeLoggerBinder());

        // configure processor
        $processor = new TelegramUpdateProcessor();

        // configure router
        $router = new StateBasedRequestRouter();
        $router->setTextRouter(new TextRouting());
        $router->setStateStorage($stateStorage);

        // bind router to processor
        $processor->setRequestRouter($router);
        //bind processor to scheme
        $this->processor = $processor;

        // proceed with parent configuration
        parent::configure();


    }
}