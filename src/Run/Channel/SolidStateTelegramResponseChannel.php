<?php


namespace Run\Channel;


use Run\Storage\UserStateStorage;
use Verse\Run\ChannelMessage\ChannelMsg;
use Verse\Telegram\Run\Channel\TelegramReplyChannel;
use Verse\Telegram\Run\Channel\Util\MessageRoute;

class SolidStateTelegramResponseChannel extends TelegramReplyChannel
{
    /**
     * @var UserStateStorage
     */
    private $stateStorage;

    public function prepare()
    {
        parent::prepare();

        $this->stateStorage = new UserStateStorage();
    }

    public function send(ChannelMsg $msg)
    {
        $result = parent::send($msg);
        if ($result) {
            $route = new MessageRoute($msg->getDestination());
            $data = (array)$msg->getChannelState()->pack(true);
            $this->stateStorage->write()->update($route->getChatId(), $data, __METHOD__);
        }
    }

}