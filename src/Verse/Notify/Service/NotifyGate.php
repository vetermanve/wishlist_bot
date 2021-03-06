<?php


namespace Verse\Notify\Service;


use Psr\Log\LoggerInterface;
use Verse\Di\Env;
use Verse\Notify\Spec\GateChannel;
use Verse\Notify\Spec\Message;
use Verse\Notify\Storage\NotifyConnectionsStorage;
use Verse\Notify\Storage\UserNotifyConnectionsStorage;
use Verse\Run\Channel\DataChannelProto;
use Verse\Run\ChannelMessage\ChannelMsg;
use Verse\Run\Util\ChannelState;
use Verse\Run\Util\Uuid;
use Verse\Scheduler\Service\TimeEventScheduler;
use Verse\Storage\StorageProto;

class NotifyGate
{
    /**
     * @var DataChannelProto[]
     */
    protected array $channelsByType = [];

    protected TimeEventScheduler $scheduler;

    /**
     * @return NotifyConnectionsStorage
     */
    public function getConnectionsStorage(): StorageProto
    {
        return new NotifyConnectionsStorage();
    }

    /**
     * @return UserNotifyConnectionsStorage
     */
    public function getUserConnectionsMapStorage(): StorageProto
    {
        return new UserNotifyConnectionsStorage();
    }

    public function getScheduler() : TimeEventScheduler
    {
        if (!isset($this->scheduler)) {
            $this->scheduler = new TimeEventScheduler();
        }
        return $this->scheduler;
    }

    public function getUserConnectionId(string $userId, string $channelUserId, string $channelType): string
    {
        return md5($userId . ':' . $channelUserId . ':' . $channelType);
    }

    protected function mapGateChannelToStorage($gateChannel): array
    {
        return [
            NotifyConnectionsStorage::ID => $gateChannel[GateChannel::ID],
            NotifyConnectionsStorage::USER_ID => $gateChannel[GateChannel::USER_ID],
            NotifyConnectionsStorage::CHANNEL_USER_ID => $gateChannel[GateChannel::CHANNEL_USER_ID],
            NotifyConnectionsStorage::CHANNEL_TYPE => $gateChannel[GateChannel::CHANNEL_TYPE],
            NotifyConnectionsStorage::KEY => $gateChannel[GateChannel::KEY],
            NotifyConnectionsStorage::SENDER => $gateChannel[GateChannel::SENDER],
            NotifyConnectionsStorage::IS_ACTIVE => $gateChannel[GateChannel::ACTIVE],
            NotifyConnectionsStorage::EXPIRE_AT => $gateChannel[GateChannel::EXPIRE_AT],
        ];
    }

    protected function mapStorageToGateChannel($storageData): array
    {
        return [
            GateChannel::ID => $storageData[NotifyConnectionsStorage::ID],
            GateChannel::USER_ID => $storageData[NotifyConnectionsStorage::USER_ID],
            GateChannel::CHANNEL_TYPE => $storageData[NotifyConnectionsStorage::CHANNEL_TYPE],
            GateChannel::CHANNEL_USER_ID => $storageData[NotifyConnectionsStorage::CHANNEL_USER_ID],
            GateChannel::KEY => $storageData[NotifyConnectionsStorage::KEY],
            GateChannel::SENDER => $storageData[NotifyConnectionsStorage::SENDER],
            GateChannel::ACTIVE => $storageData[NotifyConnectionsStorage::IS_ACTIVE],
            GateChannel::EXPIRE_AT => $storageData[NotifyConnectionsStorage::EXPIRE_AT],
        ];
    }

    public function addChannelConnection(array $channelParams)
    {
        $userId = $channelParams[GateChannel::USER_ID];

        $id = $this->getUserConnectionId(
            $channelParams[GateChannel::USER_ID],
            $channelParams[GateChannel::CHANNEL_USER_ID],
            $channelParams[GateChannel::CHANNEL_TYPE],
        );

        $channelParams[GateChannel::ID] = $id;

        $connectionsStorage = $this->getConnectionsStorage();
        $userConnectionsStorage = $this->getUserConnectionsMapStorage();

        $storageBind = $this->mapGateChannelToStorage($channelParams);
        $connWriteRes = $connectionsStorage->write()->insert($id, $storageBind, __METHOD__);
        if (!$connWriteRes) {
            return false;
        }

        $userConnectionsBind = $userConnectionsStorage->read()->get($userId, __METHOD__);
        if ($userConnectionsBind) {
            array_push($userConnectionsBind[UserNotifyConnectionsStorage::CONNECTIONS], $id);
            $connectionBindWriteRes = $userConnectionsStorage->write()->update($userId, $userConnectionsBind, __METHOD__);
        } else {
            $userConnectionsBind = [
                UserNotifyConnectionsStorage::CONNECTIONS => [$id]
            ];
            $connectionBindWriteRes = $userConnectionsStorage->write()->insert($userId, $userConnectionsBind, __METHOD__);
        }

        return !empty($connectionBindWriteRes);
    }

    public function checkUserHasConnection(string $userId, string $channelUserId, string $channelType): bool
    {
        $connectionId = $this->getUserConnectionId($userId, $channelUserId, $channelType);
        $connection = $this->getConnectionsStorage()->read()->get($connectionId, __METHOD__);
        return !empty($connection);
    }

    public function getUserConnections($userId, string $channelType, $onlyActive = false): array
    {
        $userConnections = $this->getUserConnectionsMapStorage()->read()->get($userId, __METHOD__);
        if (!$userConnections) {
            return [];
        }

        $ids = $userConnections[UserNotifyConnectionsStorage::CONNECTIONS];
        if (!$ids) {
            return [];
        }

        $connections = $this->getConnectionsStorage()->read()->mGet($ids, __METHOD__);
        if (!$connections) {
            return [];
        }

        $result = [];

        foreach ($connections as $key => $connection) {
            if ($connection[NotifyConnectionsStorage::CHANNEL_TYPE] == $channelType
                    && (!$onlyActive || $connection[NotifyConnectionsStorage::IS_ACTIVE] === true)) {
                $result[] = $this->mapStorageToGateChannel($connection);
            }
        }

        return $result;
    }

    public function scheduleUserNotification(int $delay, string $userId, string $channelType, array $body, array $meta) : bool
    {
        $time = time() + $delay;

        $message = [
            Message::TIME => $time,
            Message::USER_ID => $userId,
            Message::CHANNEL => $channelType,
            Message::BODY => $body,
            Message::META => $meta,
        ];

        try {
            return $this->getScheduler()->addEvent(
                $userId,
                $time,
                '/notify/send',
                $message
            );
        } catch (\Throwable $exception) {
            /** @var LoggerInterface $logger */
            $logger = Env::getContainer()->bootstrap(LoggerInterface::class, false);
            if ($logger) {
                $logger->error($exception->getMessage(), [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
            }
            return false;
        }
    }

    public function sendUserNotification(string $userId, string $channelType, array $body, array $meta) : bool
    {
        $connections = $this->getUserConnections($userId, $channelType, true);
        $countSent = 0;

        $channelState = new ChannelState();

        foreach ($connections as $connection) {
            $type = $connection[GateChannel::CHANNEL_TYPE];
            $channel = $this->getChannelForType($type);

            if ($channel) {
                $msgId = Uuid::v4();

                $msg = new ChannelMsg();
                $msg->setChannelState($channelState);
                $msg->setUid($msgId);
                $msg->setBody($body);
                $msg->setDestination($connection[GateChannel::CHANNEL_USER_ID]);

                foreach ($meta as $metaKey => $metaVal) {
                    $msg->setMeta($metaKey, $metaVal);
                }

                $sendResult = $channel->send($msg);

                if ($sendResult) {
                    $countSent++;
                }
            }
        }

        return $countSent > 0;
    }

    public function getChannelForType(string $channelType) : ?DataChannelProto
    {
        return $this->channelsByType[$channelType] ?? null;
    }

    public function addChannelForType(string $channelType, DataChannelProto $channel) : void
    {
        $this->channelsByType[$channelType] = $channel;
    }

    /**
     * @param TimeEventScheduler $scheduler
     */
    public function setScheduler(TimeEventScheduler $scheduler): void
    {
        $this->scheduler = $scheduler;
    }
}