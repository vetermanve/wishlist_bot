<?php


namespace Run\RequestRouter;


use Psr\Log\LoggerInterface;
use Run\RequestRouter\Spec\TelegramRequestRouterState;
use Verse\Di\Env;
use Verse\Run\RunRequest;
use Verse\Storage\StorageProto;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\RequestRouter\TelegramRouterByMessageType;

class StateBasedRequestRouter extends TelegramRouterByMessageType
{
    /**
     * @var StorageProto
     */
    private $stateStorage;

    /**
     * @var TextRouterInterface
     */
    private $textRouter;

    public function getClassByRequest(RunRequest $request)
    {
        $baseControllerClass = parent::getClassByRequest($request);

        /**
         * @var $logger LoggerInterface::class
         */
        $logger = Env::getContainer()->bootstrap('logger');

        $logger->info("ROUTER CLASS", [
            'class' => $baseControllerClass,
            'resource' => $request->getResource(),
            'test' => ($request->getResource()[0] ?? ''),
        ]);

        if ($this->stateStorage) {
            $state = $request->getChannelState();

            $chatId = (new MessageRoute($request->getReply()))->getChatId();
            $stateData = $this->stateStorage->read()->get($chatId, __METHOD__, []);

            if (!empty($stateData)) {
                $state->setPacked($stateData);
            }
        }

        var_dump('INCOMING',$request->getChannelState()->pack(true));

        // if resource is not default route returning it
        if ($baseControllerClass !== $this->buildClassName($this->_defaultModuleName, $this->_defaultControllerName)) {
            return $baseControllerClass;
        }

        // forcing text routing
        $forcingTextRoute = '';
        switch (true) {
            case isset($request->params['text'][0]) && $request->params['text'][0] === '!':
                $forcingTextRoute = $request->params['text'];
                break;
            case isset($request->data['text'][0]) && $request->data['text'][0] === '!':
                $forcingTextRoute = $request->data['text'];
                break;
            case ($request->getResource()[0] ?? '') === '!':
                $forcingTextRoute = $request->getResource();
                break;
        }

        if ($forcingTextRoute) {
            $request->params['text'] = mb_substr($forcingTextRoute, 1);;
            $textControllerClass = $this->getTextRouting($request);
            return $textControllerClass ?? $baseControllerClass;
        }

        $stateResource = $request->getChannelState()->get(TelegramRequestRouterState::RESOURCE);

        if ($stateResource) {
            if ($request->getParamOrData('text') === '') {
                $request->params['text'] = mb_substr($request->getResource(), 1);
            }

            $request->setResource($stateResource);
            $data = $request->getChannelState()->get(TelegramRequestRouterState::DATA);
            if (is_array($data)) {
                $request->data = $data + $request->data;
            }

            return parent::getClassByRequest($request);
        }

        $textControllerClass = $this->getTextRouting($request);
        if ($textControllerClass) {
            return $textControllerClass;
        }

        if (!$request->getParamOrData('text')) {
            $request->params['text'] = $request->getResource();
        }

        return $baseControllerClass;
    }

    protected function getTextRouting(RunRequest $request): ?string
    {
        if (!isset($this->textRouter)) {
            return null;
        }

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

        return null;
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