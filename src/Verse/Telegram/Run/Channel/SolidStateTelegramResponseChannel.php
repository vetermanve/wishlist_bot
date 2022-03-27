<?php


namespace Verse\Telegram\Run\Channel;


use Verse\Telegram\Run\Storage\UserStateStorage;
use Verse\Run\ChannelMessage\ChannelMsg;
use Verse\Telegram\Run\Channel\TelegramReplyChannel;
use Verse\Telegram\Run\Channel\Util\MessageRoute;

class SolidStateTelegramResponseChannel extends TelegramReplyChannel
{
    /**
     * @var UserStateStorage
     */
    private $stateStorage;

    public function send(ChannelMsg $msg)
    {
        $result = parent::send($msg);
        if ($result && $this->stateStorage) {
            $route = new MessageRoute($msg->getDestination());
            $data = (array)$msg->getChannelState()->pack(true);
            $this->stateStorage->write()->update($route->getChatId(), $data, __METHOD__);
        }
    }

    /**
     * @return UserStateStorage
     */
    public function getStateStorage(): UserStateStorage
    {
        return $this->stateStorage;
    }

    /**
     * @param UserStateStorage $stateStorage
     */
    public function setStateStorage(UserStateStorage $stateStorage): void
    {
        $this->stateStorage = $stateStorage;
    }

}