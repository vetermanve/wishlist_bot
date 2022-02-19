<?php


namespace Run\Scheme;


use Run\Channel\SolidStateTelegramResponseChannel;
use Run\RequestRouter\StateBasedRequestRouter;
use Run\Storage\UserStateStorage;
use Service\Routing\TextRouting;
use Verse\Telegram\Run\Processor\TelegramUpdateProcessor;
use Verse\Telegram\Run\Scheme\TelegramPullScheme;

class TelegramPullExtendedScheme extends TelegramPullScheme
{
    public function configure()
    {
        $stateStorage = new UserStateStorage();

        $processor = new TelegramUpdateProcessor();
        $router = new StateBasedRequestRouter();
        $router->setTextRouter(new TextRouting());
        $router->setStateStorage($stateStorage);

        $processor->setRequestRouter($router);
        $this->processor = $processor;
        parent::configure();

        $channel = new SolidStateTelegramResponseChannel();
        $channel->setStateStorage($stateStorage);

        $this->core->setDataChannel($channel);
    }
}