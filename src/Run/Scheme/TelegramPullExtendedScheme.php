<?php


namespace Run\Scheme;


use Run\Channel\SolidStateTelegramResponseChannel;
use Run\RequestRouter\StateBasedRequestRouter;
use Verse\Telegram\Run\Processor\TelegramUpdateProcessor;
use Verse\Telegram\Run\Scheme\TelegramPullScheme;

class TelegramPullExtendedScheme extends TelegramPullScheme
{
    public function configure()
    {
        $processor = new TelegramUpdateProcessor();
        $processor->setRequestRouter(new StateBasedRequestRouter());
        $this->processor = $processor;
        parent::configure();

        $this->core->setDataChannel(new SolidStateTelegramResponseChannel());
    }
}