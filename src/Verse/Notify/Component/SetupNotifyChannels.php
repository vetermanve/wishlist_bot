<?php


namespace Verse\Notify\Component;


use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Run\Channel\DataChannelProto;
use Verse\Run\Component\RunComponentProto;

class SetupNotifyChannels extends RunComponentProto
{
    /**
     * @var DataChannelProto[]
     */
    private array $channels = [];

    public function run()
    {

        /* @var NotifyGate $gate */
        $gate = Env::getContainer()->bootstrap(NotifyGate::class);
        foreach ($this->channels as $type => $channel) {
            $channel->follow($this);
            $channel->prepare();
            $gate->addChannelForType($type, $channel);
        }
    }

    /**
     * @return DataChannelProto[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @param DataChannelProto[] $channels
     */
    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }

    /**
     * @param string $type
     * @link ChannelType - types from here
     *
     * @param DataChannelProto $channel
     */
    public function addChannel(string $type, DataChannelProto $channel) : void
    {
        $this->channels[$type] = $channel;
    }
}