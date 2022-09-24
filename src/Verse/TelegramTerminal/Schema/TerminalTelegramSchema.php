<?php


namespace Verse\TelegramTerminal\Schema;


use Verse\Telegram\Run\Scheme\TelegramPullExtendedScheme;
use Verse\Telegram\Run\Storage\UserStateStorage;
use Verse\TelegramTerminal\Channel\TelegramTerminalOutput;
use Verse\TelegramTerminal\Provider\TelegramTerminalProvider;

class TerminalTelegramSchema extends TelegramPullExtendedScheme
{

    public function configure()
    {
        parent::configure();

        $stateStorage = new UserStateStorage();

        // configure input
        $provider = new TelegramTerminalProvider();
        $provider->setStateStorage($stateStorage);
        $this->core->setProvider($provider);

        // configure output
        $channel = new TelegramTerminalOutput();
        $channel->setStateStorage($stateStorage);
        $this->core->setDataChannel($channel);
    }
}