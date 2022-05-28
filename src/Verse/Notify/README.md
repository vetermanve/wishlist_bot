# Notify gate is an app for registering notification channels and sending user notifications

## Step 1 register user notification channel

```php
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ConnectionTypes;
use Verse\Notify\Spec\GateChannel;

$userId = 123; // some your user id;


$gate = new NotifyGate();
$gate->addChannel([
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CONNECTION => ConnectionTypes::TELEGRAM,
    GateChannel::CHANNEL_ID => '${telegramUserId}',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_bot', // binding sender
    GateChannel::ACTIVE => true, // use this field for channel authorisation state
    GateChannel::EXPIRE_AT => null // not expiring
]);

$gate->addChannel([
    GateChannel::CONNECTION => ConnectionTypes::SMS,
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_ID => '+79819819641111',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'AUTH_SENDER', // binding sender
    GateChannel::ACTIVE => true, // number was verified
    GateChannel::EXPIRE_AT => null // not expiring
]);

$gate->addChannel([
    GateChannel::CONNECTION => ConnectionTypes::EMAIL,
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_ID => 'me@vetermanve.com',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'noreply@wishlistbot.com', // binding sender
    GateChannel::ACTIVE => true, // email was verified
    GateChannel::EXPIRE_AT => null // not expiring
]);

// just idea
$gate->addChannel([
    GateChannel::CONNECTION => ConnectionTypes::VERSE_TERMINAL, // user terminal session
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_ID => 'pid@host',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_app', // binding sender
    GateChannel::ACTIVE => true, // are user online?
    GateChannel::EXPIRE_AT => time() + 6400 // should have connection recheck after expiration 
]);

// just idea
$gate->addChannel([
    GateChannel::CONNECTION => ConnectionTypes::VERSE_WS_NODE, // user terminal session
    GateChannel::USER_ID => $userId, // your system user id
    GateChannel::CHANNEL_ID => 'pid@host',
    GateChannel::KEY => '', // authorisation key if necessary
    GateChannel::SENDER => 'wishlist_app', // binding sender
    GateChannel::ACTIVE => true, // are user online?
    GateChannel::EXPIRE_AT => time() + 6400 // should have connection recheck after expiration 
]);




```