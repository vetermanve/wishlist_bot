# Notify gate is an app for registering notification channels and sending user notifications

## Step 1 register user notification channel

```php
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;

$userId = 123; // some your user id;


$gate = new NotifyGate();
$gate->addChannelConnection([
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
    GateChannel::CHANNEL_USER_ID => '${telegramUserId}',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_bot', // binding sender
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

$gate->addChannelConnection([
    GateChannel::CHANNEL_TYPE => ChannelType::EMAIL,
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_USER_ID => 'me@vetermanve.com',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'noreply@wishlistbot.com', // binding sender
    GateChannel::ACTIVE => true, // email was verified
    GateChannel::EXPIRE_AT => null // not expiring
]);

// just idea
$gate->addChannelConnection([
    GateChannel::CHANNEL_TYPE => ChannelType::VERSE_TERMINAL, // user terminal session
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_USER_ID => 'pid@host',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_app', // binding sender
    GateChannel::ACTIVE => true, // are user online?
    GateChannel::EXPIRE_AT => time() + 6400 // should have connection recheck after expiration 
]);

// just idea
$gate->addChannelConnection([
    GateChannel::CHANNEL_TYPE => ChannelType::VERSE_WS_NODE, // user terminal session
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_USER_ID => 'pid@host',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_app', // binding sender
    GateChannel::ACTIVE => true, // are user online?
    GateChannel::EXPIRE_AT => time() + 6400 // should have connection recheck after expiration 
]);
```

## Step 2 - Check user has connection if necessary

```php
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;

$userId = 123; // some your user id;
$userTelegramId = md5($userId);

$gate = new NotifyGate();

$writeResult = $gate->addChannelConnection([
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
    GateChannel::CHANNEL_USER_ID => $userTelegramId,
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'test_bot', // binding sender
    GateChannel::ACTIVE => true, // use this field for channel authorisation state
    GateChannel::EXPIRE_AT => null // not expiring
]);

$hasConnection = $gate->checkUserHasConnection($userId, $userTelegramId, ChannelType::TELEGRAM);
```

## Step 3 - Get available connection for type

```php
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;

$userId = 123; // some your user id;
$userTelegramId = md5($userId);

$gate = new NotifyGate();

$writeResult = $gate->addChannelConnection([
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
    GateChannel::CHANNEL_USER_ID => $userTelegramId,
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'test_bot', // binding sender
    GateChannel::ACTIVE => true, // use this field for channel authorisation state
    GateChannel::EXPIRE_AT => null // not expiring
]);

$connections = $gate->getUserConnections($userId, ChannelType::TELEGRAM);
```

# Step 4 - Send notification to user

```php
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;

$gate = new NotifyGate();

$userId = 123; // some your user id;
$senderId = 'test_sender@someservice.com';

$connectionAddResult = $gate->addChannelConnection([
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_TYPE => ChannelType::EMAIL,
    GateChannel::CHANNEL_USER_ID => 'superman@test.mail',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => $senderId, // binding sender
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

$isNotificationSent = $gate->sendUserNotification($userId, ChannelType::EMAIL, $body, $meta);
```
