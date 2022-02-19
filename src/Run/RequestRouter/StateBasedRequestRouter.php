<?php


namespace Run\RequestRouter;


use Psr\Log\LoggerInterface;
use Run\RequestRouter\Spec\TelegramRequestRouterState;
use Run\Storage\UserStateStorage;
use Verse\Di\Env;
use Verse\Run\RunRequest;
use Verse\Storage\StorageProto;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\RequestRouter\TelegramRouterByMessageType;

class StateBasedRequestRouter extends TelegramRouterByMessageType
{
    private $defaultRouteController;

    /**
     * @var StorageProto
     */
    private $stateStorage;

    /**
     * @var TextRouterInterface
     */
    private $textRouter;

    /**
     * StateBasedRequestRouter constructor.
     */
    public function __construct()
    {
        $this->defaultRouteController = '\\' . self::DEFAULT_MODULE . '\\Controller\\' . self::DEFAULT_CONTROLLER;
    }

    public function getClassByRequest(RunRequest $request)
    {
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

        if ($this->stateStorage) {
            $state = $request->getChannelState();

            $chatId = (new MessageRoute($request->getReply()))->getChatId();
            $stateData = $this->stateStorage->read()->get($chatId, __METHOD__, []);

            if (!empty($stateData)) {
                $state->setPacked($stateData);
            }
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
        } elseif (isset($this->textRouter)) {
            $textRouterResult = $this->textRouter->getClassAndData($request);
            if (is_array($textRouterResult)) {
                [$resource, $data] = $textRouterResult;

                if ($resource && is_string($resource)) {
                    if (is_array($data)) {
                        $request->data = $data + $request->data;
                    }
                    $request->setResource($resource);
                    return parent::getClassByRequest($request);
                }
            }
        }

        return $controllerClass;
    }


    private function isDefaultRoute($controller) : bool  {
        return $this->defaultRouteController === $controller;
    }

    /**
     * @return TextRouterInterface
     */
    public function getTextRouter(): TextRouterInterface
    {
        return $this->textRouter;
    }

    /**
     * @param TextRouterInterface $textRouter
     */
    public function setTextRouter(TextRouterInterface $textRouter): void
    {
        $this->textRouter = $textRouter;
    }

    /**
     * @return StorageProto
     */
    public function getStateStorage(): StorageProto
    {
        return $this->stateStorage;
    }

    /**
     * @param StorageProto $stateStorage
     */
    public function setStateStorage(StorageProto $stateStorage): void
    {
        $this->stateStorage = $stateStorage;
    }
}