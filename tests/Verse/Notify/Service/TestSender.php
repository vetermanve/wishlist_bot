<?php


namespace Verse\Notify\Service;


use Verse\Notify\Sender\AbstractNotifySender;
use function PHPUnit\Framework\assertContainsOnlyInstancesOf;
use function PHPUnit\Framework\assertInstanceOf;

class TestSender extends AbstractNotifySender
{
    public const CHANNEL_USER_ID = 'ch_u_id';
    public const SENDER_ID = 'sender_id';
    public const BODY = 'body';
    public const META = 'meta';

    private $lastMessage = [];

    /**
     * @return array
     */
    public function getLastMessage(): array
    {
        return $this->lastMessage;
    }

    protected function doSendMessage(string $channelUserId, string $senderId, array $body, array $meta) : bool
    {
        $this->lastMessage = [
            self::CHANNEL_USER_ID => $channelUserId,
            self::SENDER_ID => $senderId,
            self::BODY => $body,
            self::META => $meta
        ];

        return true;
    }
}