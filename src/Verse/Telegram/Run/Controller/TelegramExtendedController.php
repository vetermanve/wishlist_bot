<?php


namespace Verse\Telegram\Run\Controller;


use Verse\Telegram\Run\RequestRouter\ResourceCompiler;
use Verse\Telegram\Run\RequestRouter\Spec\TelegramRequestRouterState;
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
        $this->setState(TelegramRequestRouterState::NEXT_RESOURCE, $resource, $ttl);
        $this->setState(TelegramRequestRouterState::NEXT_RESOURCE_DATA, $data, $ttl);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

    protected function setNextResourceByClass($className, $data = null, $ttl = null) {
        $resource = $this->getResourceByClass($className);
        return $this->setNextResource($resource, $data, $ttl);
    }

    protected function r($className) {
        return $this->getResourceByClass($className);
    }

    protected function getResourceByClass($className) {
        return ResourceCompiler::fromClassName($className);
    }
}