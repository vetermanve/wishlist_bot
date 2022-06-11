<?php


namespace Verse\Telegram\Run\Scheme;


use Verse\Notify\Component\SetupNotifyGate;
use Verse\Run\Component\CreateDependencyContainer;
use Verse\Run\Component\UnexpectedShutdownHandler;
use Verse\Run\Schema\PreconfiguredSchemaProto;
use Verse\Telegram\Run\Channel\SolidStateTelegramResponseChannel;
use Verse\Telegram\Run\Component\RuntimeLoggerBinder;
use Verse\Telegram\Run\Provider\TelegramGetUpdatesProvider;
use Verse\Telegram\Run\RequestRouter\StateBasedRequestRouter;
use Verse\Telegram\Run\Storage\UserStateStorage;
use Service\Routing\TextRouting;
use Verse\Telegram\Run\Processor\TelegramUpdateProcessor;

class TelegramPullExtendedScheme extends PreconfiguredSchemaProto
{
    public function configure()
    {
        // setting provider
        $provider = new TelegramGetUpdatesProvider();
        $this->core->setProvider($provider);

        // basic components
        $this->core->addComponent(new UnexpectedShutdownHandler());
        $this->core->addComponent(new CreateDependencyContainer());

        // add component to drop notifications
        $this->core->addComponent(new SetupNotifyGate());
        // add component to support global logs
        $this->core->addComponent(new RuntimeLoggerBinder());

        // configure telegram channel
        $channel = new SolidStateTelegramResponseChannel();
        //// configure and bind state storage
        $stateStorage = new UserStateStorage();
        $channel->setStateStorage($stateStorage);
        //// bind telegram channel to core
        $this->core->setDataChannel($channel);

        // configuring processor
        $processor = new TelegramUpdateProcessor();

        //// configure router
        $router = new StateBasedRequestRouter();
        $router->setTextRouter(new TextRouting());
        $router->setStateStorage($stateStorage);

        //// bind router to processor
        $processor->setRequestRouter($router);
        //// bind processor to scheme
        $this->core->setProcessor($processor);

        $this->_addCustomComponents();
    }
}