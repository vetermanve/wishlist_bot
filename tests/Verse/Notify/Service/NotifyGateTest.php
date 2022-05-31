<?php


namespace Verse\Notify\Service;


use App\Done\Controller\Done;
use App\Item\Controller\All;
use App\Item\Controller\EditMode;
use App\Landing\Controller\Landing;
use PHPUnit\Framework\TestCase;
use Verse\Notify\Sender\AbstractNotifySender;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;
use Verse\Run\Channel\DataChannelProto;
use Verse\Run\Channel\MemoryStoreChannel;
use Verse\Telegram\Run\RequestRouter\ResourceCompiler;

class NotifyGateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $path = explode(DIRECTORY_SEPARATOR, getcwd());
        $key = array_search('tests', $path);
        if ($key) {
            $path = array_slice($path, 0, $key+1);
            chdir(implode(DIRECTORY_SEPARATOR, $path));
        }

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $gate = new NotifyGate();
        $ucs = $gate->getUserConnectionsMapStorage();
        $items = $ucs->search()->find([], 1000000, __METHOD__);
        $ucs->write()->removeBatch(array_column($items, 'id'), __METHOD__);

        $cs = $gate->getConnectionsStorage();
        $items = $cs->search()->find([], 1000000, __METHOD__);
        $cs->write()->removeBatch(array_column($items, 'id'), __METHOD__);
    }

    public function testAddNewGateToUser()
    {
        $userId = crc32(microtime());

        $gate = new NotifyGate();

        $res1 = $gate->addChannelConnection([
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
            GateChannel::CHANNEL_USER_ID => md5($userId),
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'test_bot', // binding sender
            GateChannel::ACTIVE => true, // use this field for channel authorisation state
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res1);

        $res2 = $gate->addChannelConnection([
            GateChannel::CHANNEL_TYPE => ChannelType::SMS,
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_USER_ID => '+79819819641111',
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'AUTH_SENDER', // binding sender
            GateChannel::ACTIVE => true, // number was verified
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res2);
    }

    public function testAddNew2GatesToUser()
    {
        $userId = crc32(microtime());

        $gate = new NotifyGate();

        $res = $gate->addChannelConnection([
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
            GateChannel::CHANNEL_USER_ID => md5($userId),
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'test_bot', // binding sender
            GateChannel::ACTIVE => true, // use this field for channel authorisation state
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $gate->addChannelConnection([
            GateChannel::CHANNEL_TYPE => ChannelType::SMS,
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_USER_ID => '+79819819641111',
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'AUTH_SENDER', // binding sender
            GateChannel::ACTIVE => true, // number was verified
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res);
    }

    public function testUserChannelExist() {
        $userId = (string) crc32(microtime());


        $gate = new NotifyGate();

        $res1 = $gate->addChannelConnection([
            GateChannel::CHANNEL_TYPE => ChannelType::SMS,
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_USER_ID => '+79819819641111',
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'AUTH_SENDER', // binding sender
            GateChannel::ACTIVE => true, // number was verified
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res1, 'User Connection written');
        $has1 = $gate->checkUserHasConnection($userId, '+79819819641111', ChannelType::SMS);
        $this->assertTrue($has1, 'User Connection found');

        $userTelegramId = md5($userId);
        $res2 = $gate->addChannelConnection([
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
            GateChannel::CHANNEL_USER_ID => $userTelegramId,
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'test_bot', // binding sender
            GateChannel::ACTIVE => true, // use this field for channel authorisation state
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res2, 'User Connection written');
        $has2 = $gate->checkUserHasConnection($userId, $userTelegramId, ChannelType::TELEGRAM);
        $this->assertTrue($has2, 'User Connection found');
    }


    public function testGetUserConnections() {
        $userId = (string) crc32(microtime());
        $gate = new NotifyGate();

        // add user1 sms connection
        $phone1 = '+79819819641111';
        $connSMS1 = [
            GateChannel::CHANNEL_TYPE => ChannelType::SMS,
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_USER_ID => $phone1,
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'AUTH_SENDER', // binding sender
            GateChannel::ACTIVE => true, // number was verified
            GateChannel::EXPIRE_AT => null // not expiring
        ];

        $res1 = $gate->addChannelConnection($connSMS1);

        $this->assertTrue($res1, 'User Connection written');

        $has1 = $gate->checkUserHasConnection($userId, $phone1, ChannelType::SMS);
        $this->assertTrue($has1, 'User Connection found');

        // add user 2 sms connection
        $phoneNumber2 = '+79819819641122';
        $connSMS2 = [
            GateChannel::CHANNEL_TYPE => ChannelType::SMS,
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_USER_ID => $phoneNumber2,
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'AUTH_SENDER', // binding sender
            GateChannel::ACTIVE => true, // number was verified
            GateChannel::EXPIRE_AT => null // not expiring
        ];

        $res2 = $gate->addChannelConnection($connSMS2);
        $this->assertTrue($res2, 'User Connection written');

        $has2 = $gate->checkUserHasConnection($userId, $phoneNumber2, ChannelType::SMS);
        $this->assertTrue($has2, 'User Connection found');

        // Compose check object
        $connSMS1 = [GateChannel::ID => $gate->getUserConnectionId($userId, $phone1, ChannelType::SMS)] + $connSMS1;
        $connSMS2 = [GateChannel::ID => $gate->getUserConnectionId($userId, $phoneNumber2, ChannelType::SMS)] + $connSMS2;

        // add misc connection
        $userTelegramId = md5($userId);
        $res3 = $gate->addChannelConnection([
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
            GateChannel::CHANNEL_USER_ID => $userTelegramId,
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => 'test_bot', // binding sender
            GateChannel::ACTIVE => true, // use this field for channel authorisation state
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($res3, 'User Connection written');
        $has3 = $gate->checkUserHasConnection($userId, $userTelegramId, ChannelType::TELEGRAM);
        $this->assertTrue($has3, 'User Connection found');

        // main search logic

        $connections = $gate->getUserConnections($userId, ChannelType::SMS);
        $this->assertCount(2, $connections);

        $this->assertEquals([$connSMS1, $connSMS2], $connections);
    }

    public function testSendUserMessage()
    {
        $channelType = 'test_channel';

        // creating gate
        $gate = new NotifyGate();

        // adding new channel
        $channel = new MemoryStoreChannel();

        $this->assertInstanceOf(DataChannelProto::class, $channel);
        $gate->addChannelForType($channelType, $channel);

        // adding user channel connection
        $userId = '456aas';
        $channelUserId = $userId.'@'.$channelType;
        $senderId = 'test_sender@' . $channelType;

        $connectionAddResult = $gate->addChannelConnection([
            GateChannel::USER_ID => $userId, // your system user id
            GateChannel::CHANNEL_TYPE => $channelType,
            GateChannel::CHANNEL_USER_ID => $channelUserId,
            GateChannel::KEY => '', // authorisation key if necessary
            GateChannel::SENDER => $senderId, // binding channel
            GateChannel::ACTIVE => true, // use this field for channel authorisation state
            GateChannel::EXPIRE_AT => null // not expiring
        ]);

        $this->assertTrue($connectionAddResult, 'User Connection written');

        $body = [
            'text' => 'Blablalba',
            'buttons' => [
                '1' => 'nana',
            ]
        ];

        $meta = [
            'page' => 1,
            'render' => 'blue',
        ];

        $resSend = $gate->sendUserNotification($userId, $channelType, $body, $meta);
        $resultedMessage =  $channel->getMessage();
        $this->assertNotEmpty($resultedMessage);

        $expected = [
            TestSender::CHANNEL_USER_ID => $channelUserId,
            TestSender::BODY => $body,
            TestSender::META => $meta
        ];

        $result = [
            TestSender::CHANNEL_USER_ID => $resultedMessage->getDestination(),
            TestSender::BODY => $resultedMessage->getBody(),
            TestSender::META => $resultedMessage->getAllMeta()
        ];

        $this->assertEquals($expected, $result);
    }


}