<?php


namespace Verse\Telegram\Run\Component;


use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Run\Channel\DataChannelProto;
use Verse\Run\Component\RunComponentProto;

class SetupTelegramNotifyGate extends RunComponentProto
{
    private DataChannelProto $telegramChannel;


    public function run()
    {
        $telegramChannel = $this->telegramChannel;
        $telegramChannel->follow($this);

        Env::getContainer()->setModule(NotifyGate::class, function () use ($telegramChannel) {
            $gate = new NotifyGate();
            $telegramChannel->prepare();
            $gate->addChannelForType(ChannelType::TELEGRAM, $telegramChannel);
            return $gate;
        });
    }

    /**
     * @return DataChannelProto
     */
    public function getTelegramChannel(): DataChannelProto
    {
        return $this->telegramChannel;
    }

    /**
     * @param DataChannelProto $telegramChannel
     */
    public function setTelegramChannel(DataChannelProto $telegramChannel): void
    {
        $this->telegramChannel = $telegramChannel;
    }
}