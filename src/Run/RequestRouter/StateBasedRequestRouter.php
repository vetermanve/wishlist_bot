<?php


namespace Run\RequestRouter;


use Psr\Log\LoggerInterface;
use Run\RequestRouter\Spec\TelegramRequestRouterState;
use Run\Storage\UserStateStorage;
use Verse\Di\Env;
use Verse\Run\RunRequest;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\RequestRouter\TelegramRouterByMessageType;

class StateBasedRequestRouter extends TelegramRouterByMessageType
{
    private $defaultRouteController;

    private $stateStorage;

    /**
     * StateBasedRequestRouter constructor.
     */
    public function __construct()
    {
        $this->defaultRouteController = '\\' . self::DEFAULT_MODULE . '\\Controller\\' . self::DEFAULT_CONTROLLER;
        $this->stateStorage = new UserStateStorage();
    }

    public function getClassByRequest(RunRequest $request)
    {
        $state = $request->getChannelState();

        $chatId = (new MessageRoute($request->getReply()))->getChatId();
        $stateData = $this->stateStorage->read()->get($chatId, __METHOD__, []);

        if (!empty($stateData)) {
            $state->setPacked($stateData);
        }

        $controllerClass = parent::getClassByRequest($request);

        /**
         * @var $logger LoggerInterface::class
         */
        $logger = Env::getContainer()->bootstrap('logger');

        $logger->info("ROUTER CLASS", [
            'class' => $controllerClass,
        ]);
        // all ok, resource was found
        if (!$this->isDefaultRoute($controllerClass)) {
            return $controllerClass;
        }

        $resource = $request->getChannelState()->get(TelegramRequestRouterState::RESOURCE);

        $logger->debug("");
        if ($resource)  {
            $request->setResource($resource);
            $data = $request->getChannelState()->get(TelegramRequestRouterState::DATA);
            if (is_array($data)) {
                $request->data = $data + $request->data;
            }

            return parent::getClassByRequest($request);
        }

        return $controllerClass;
    }


    private function isDefaultRoute($controller) : bool  {
        return $this->defaultRouteController === $controller;
    }
}