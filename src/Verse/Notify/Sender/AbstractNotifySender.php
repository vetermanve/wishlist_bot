<?php


namespace Verse\Notify\Sender;


abstract class AbstractNotifySender
{
    public function sendMessage(string $channelUserId, string $senderId, array $body, array $meta) : bool {
        return $this->doSendMessage($channelUserId, $senderId, $body, $meta);
    }

    abstract protected function doSendMessage(string $channelUserId, string $senderId, array $body, array $meta) : bool;
}