<?php


namespace App\Task\Controller;


use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\ChannelType;
use Verse\Notify\Spec\GateChannel;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Controller\TelegramExtendedController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Task extends TelegramExtendedController
{
    public function text_message(): ?TelegramResponse
    {
        $userId = $this->getUserId();

        $this->log('Has user', [$this->requestWrapper->getParam('from')]);

        /** @var $gate NotifyGate::class  */
        $gate = Env::getContainer()->bootstrap(NotifyGate::class);

        $route = new MessageRoute();
        $route->setChannel('tg');
        $route->setChatId($userId);
        $route->setAppear(MessageRoute::APPEAR_NEW_MESSAGE);
        $tgRoute = $route->packString();

        $hasTelegramConnection = $gate->checkUserHasConnection($userId, $tgRoute, ChannelType::TELEGRAM);
        if (!$hasTelegramConnection) {
            $telegramConnectionData = [
                GateChannel::CHANNEL_TYPE => ChannelType::TELEGRAM,
                GateChannel::USER_ID => $userId, // your system user id
                GateChannel::CHANNEL_USER_ID => $tgRoute,
                GateChannel::KEY => '', // authorisation key if necessary
                GateChannel::SENDER => 'we_wishlist', // binding sender
                GateChannel::ACTIVE => true, // number was verified
                GateChannel::EXPIRE_AT => null // not expiring
            ];

            $connectionCreated = $gate->addChannelConnection($telegramConnectionData);
        }

        $notificationSent = $gate->sendUserNotification($userId, ChannelType::TELEGRAM, [
            'text' => "helloo!",
        ], []);


        return $this->textResponse( $notificationSent ? 'Создана фоновая задача' : 'Не создана');
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

}