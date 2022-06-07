<?php


namespace App\Notify\Controller;


use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Notify\Spec\Message;
use Verse\Run\Controller\SimpleController;

class Notify extends SimpleController
{
    public function get()
    {
        $userId  = $this->p(Message::USER_ID);
        $channel = $this->p(Message::CHANNEL);
        $body    = $this->p(Message::BODY);
        $meta    = $this->p(Message::META);

        /** @var $gate NotifyGate::class  */
        $gate = Env::getContainer()->bootstrap(NotifyGate::class);

        $notificationSent = $gate->sendUserNotification(
            $userId,
            $channel,
            $body,
            $meta
        );

        return [
            'result' =>  $notificationSent ? 'Отправлено' : 'Не отправлено',
            'eventData' => $this->requestWrapper->getRequestParams(),
        ];
    }
}