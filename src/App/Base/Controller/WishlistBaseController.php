<?php


namespace App\Base\Controller;


use App\Start\Controller\Start;
use Verse\Renderer\Controller\PageRenderTrait;
use Verse\Telegram\Run\RequestRouter\ResourceCompiler;
use Verse\Telegram\Run\RequestRouter\Spec\TelegramRequestRouterState;
use Verse\Telegram\Run\Controller\TelegramResponse;
use Verse\Telegram\Run\Controller\TelegramRunController;

class WishlistBaseController extends TelegramRunController
{
    const USER_STATE_TTL = 2592000; // 30 days

    use PageRenderTrait;

//    public function run()
//    {
//        // run action
//        $result = parent::run();
//        // save last resource
//        $this->setLastResource();
//        return $result;
//    }

    protected function rememberBrowseBackResource()
    {
        $this->setState(TelegramRequestRouterState::LAST_RESOURCE, $this->r(static::class), self::USER_STATE_TTL);
    }

    protected function getBrowseBackResource() : string
    {
        return $this->getState(TelegramRequestRouterState::LAST_RESOURCE) ?? $this->r(Start::class);
    }

    protected function getLayout(): string
    {
        return 'clear_layout';
    }

    public function getUserId()
    {
        $form = $this->requestWrapper->getParam('from');

        if (isset($form, $form['id'])) {
            return $form['id'];
        }

        return null;
    }

    public function setNextResource($resource, $data = null)
    {
        $this->setState(TelegramRequestRouterState::NEXT_RESOURCE, $resource, self::USER_STATE_TTL);
        $this->setState(TelegramRequestRouterState::NEXT_RESOURCE_DATA, $data, self::USER_STATE_TTL);
    }

    public function callback_query(): ?TelegramResponse
    {
        return $this->text_message();
    }

    protected function setNextResourceByClass($className, $data = null, $ttl = null)
    {
        $resource = $this->getResourceByClass($className);
        $this->setNextResource($resource, $data);
    }

    protected function r($className)
    {
        return $this->getResourceByClass($className);
    }

    protected function getResourceByClass($className)
    {
        return ResourceCompiler::fromClassName($className);
    }
    
}