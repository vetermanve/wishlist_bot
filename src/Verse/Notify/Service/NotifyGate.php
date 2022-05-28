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


    public function addChannel(array $channelParams)
    {
        $id = Uuid::v4();
        $channelParams[GateChannel::ID] = $id;
        $userId = $channelParams[GateChannel::USER_ID];

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

    public function mapGateChannelToStorage($gateChannel)
    {
        /*
        GateChannel::CONNECTION => ConnectionTypes::VERSE_WS_NODE, // user terminal session
        GateChannel::USER_ID => $userId, // your system user id
        GateChannel::CHANNEL_ID => 'pid@host',
        GateChannel::KEY => '', // authorisation key if necessary
        GateChannel::SENDER => 'wishlist_app', // binding sender
        GateChannel::ACTIVE => true, // are user online?
        GateChannel::EXPIRE_AT => time() + 6400 // should have connection recheck after expiration
         * */

        return [
            NotifyConnectionsStorage::ID => $gateChannel[GateChannel::ID],
            NotifyConnectionsStorage::USER_ID => $gateChannel[GateChannel::USER_ID],
            NotifyConnectionsStorage::CHANNEL_ID => $gateChannel[GateChannel::CHANNEL_ID],
            NotifyConnectionsStorage::KEY => $gateChannel[GateChannel::KEY],
            NotifyConnectionsStorage::SENDER => $gateChannel[GateChannel::SENDER],
            NotifyConnectionsStorage::IS_ACTIVE => $gateChannel[GateChannel::ACTIVE],
            NotifyConnectionsStorage::EXPIRE_AT => $gateChannel[GateChannel::EXPIRE_AT],
        ];
    }
}