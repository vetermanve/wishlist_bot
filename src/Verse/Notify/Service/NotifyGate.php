<?php


namespace Verse\Notify\Service;


use Verse\Notify\Spec\GateChannel;
use Verse\Notify\Storage\NotifyConnectionsStorage;
use Verse\Notify\Storage\UserNotifyConnectionsStorage;
use Verse\Run\Util\Uuid;

class NotifyGate
{
    /**
     * @return NotifyConnectionsStorage
     */
    public function getConnectionsStorage()
    {
        return new NotifyConnectionsStorage();
    }

    /**
     * @return UserNotifyConnectionsStorage
     */
    public function getUserConnectionsMapStorage()
    {
        return new UserNotifyConnectionsStorage();
    }

    public function getUserConnectionId(string $userId, string $channelUserId, string $channelType)
    {
        return md5($userId . ':' . $channelUserId . ':' . $channelType);
    }

    public function mapGateChannelToStorage($gateChannel): array
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

    public function mapStorageToGateChannel($storageData): array
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
}