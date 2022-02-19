<?php


namespace Run\Controller;


use Run\RequestRouter\Spec\TelegramRequestRouterState;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class TelegramExtendedController extends TelegramRunController
{
    public function getUserId ()  {
        $form = $this->requestWrapper->getParam('from');

        if (isset($form, $form['id'])) {
            return  $form['id'];
        }

        return null;
    }

    public function setNextResource($resource, $data = null, $ttl = null) {
        $this->setState(TelegramRequestRouterState::RESOURCE, $resource, $ttl);
        $this->setState(TelegramRequestRouterState::DATA, $data, $ttl);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }
}