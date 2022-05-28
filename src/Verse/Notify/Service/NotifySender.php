<?php


namespace Verse\Notify\Service;


use Verse\Notify\Render\RenderFactory;
use Verse\Notify\Storage\NotifyConnectionsStorage;
use Verse\Notify\Storage\UserNotifyConnectionsStorage;

class NotifySender
{
    /**
     * @var UserNotifyConnectionsStorage
     */
    private $userConnections;

    /**
     * @var NotifyConnectionsStorage
     */
    private $connections;

    /**
     * @var RenderFactory
     */
    private $rederFactory;

    /**
     * NotifySender constructor.
     * @param UserNotifyConnectionsStorage $userConnections
     * @param NotifyConnectionsStorage $connections
     */
    public function __construct(UserNotifyConnectionsStorage $userConnections = null, NotifyConnectionsStorage $connections = null)
    {
        if ($userConnections) {
            $this->userConnections = $userConnections;
        } else {
            $this->userConnections = new UserNotifyConnectionsStorage();
        }

        if ($connections) {
            $this->connections = $connections;
        } else {
            $this->connections = new NotifyConnectionsStorage();
        }

        $this->rederFactory = new RenderFactory();
    }


    /**
     * @param $userId
     * @param $data
     * @param array $channels
     * @param array $channelMeta
     * @return int
     */
    public function sendMessage($userId, $data, $channels = [], $channelMeta = [])
    {
        $userToConnections = $this->userConnections->read()->get($userId, __METHOD__);
        if (!$userToConnections || empty($userToConnections[UserNotifyConnectionsStorage::CONNECTIONS])) {
            return 0;
        }

        $connections = $this->connections->read()->mGet($userToConnections[UserNotifyConnectionsStorage::CONNECTIONS], __METHOD__);
        if (!$connections) {
            return 0;
        }

        $factory = new RenderFactory();

        foreach ($connections as $connection) {
            $type = $connection[NotifyConnectionsStorage::CHANNEL_TYPE];

//            $renderer = $this->rederFactory->getRendererByConnectionType($type);
//            if ($renderer) {
//                  $messageData = $renderer->setMeta($channelMeta);
//            }
        }
    }
}