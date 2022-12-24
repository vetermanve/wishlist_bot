<?php


namespace Verse\TelegramTerminal\Provider;


use Verse\Run\ChannelMessage\ChannelMsg;
use Verse\Run\Provider\RequestProviderProto;
use Verse\Run\RunContext;
use Verse\Run\RunRequest;
use Verse\Run\Spec\HttpRequestMetaSpec;
use Verse\Run\Util\ChannelState;
use Verse\Run\Util\Uuid;
use Verse\Storage\StorageProto;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Spec\MessageType;
use Verse\TelegramTerminal\Channel\TelegramTerminalOutput;

class TelegramTerminalProvider extends RequestProviderProto
{
    protected ?int $userId = null;

    /**
     * @var ?StorageProto
     */
    private ?StorageProto $stateStorage;

    public function prepare()
    {

    }

    public function run()
    {
        $this->_prepareUserId();

        $this->processLine('/start');
//        $this->processLine('/start  12312313');

        while (true) {
            $line = \readline('>>> ');
            $this->processLine($line);
        }
    }

    protected function processLine($message): void
    {
        $route = new MessageRoute();
        $route->setChatId($this->userId);

        if ($this->execProviderCommand($message)) {
            return;
        }

        $command = $this->_detectButtonCall($message, $route);
        if ($command) {
            $method = MessageType::CALLBACK_QUERY;
            $message = $command;
        } else {
            $command = $this->_detectCommand($message);
            $method = MessageType::TEXT_MESSAGE;
        }

        $resource = '/';
        $params = [];
        if ($command !== '') {
            $resource = parse_url($command, PHP_URL_PATH);
            $paramsSting = parse_url($command, PHP_URL_QUERY);
            if ($paramsSting) {
                parse_str($paramsSting, $params);
            }

            $message = trim(mb_substr($message, mb_strlen($command)));
        }

        $this->runtime->debug('Got command', ['_c' => $command, '_t' => $method,]);

        $req = new RunRequest(Uuid::v4(), $resource, $route->packString());
        $req->data = ['text' => $message];
        $req->params = [
                'from' => [
                    'id' => $this->userId,
                    'first_name' => 'U'.$this->userId,
                ],
            ] + $params;
        $req->meta[HttpRequestMetaSpec::REQUEST_METHOD] = $method;

        $this->core->process($req);
    }

    protected function _detectButtonCall($message, MessageRoute $route): ?string
    {
        if (!isset($this->stateStorage)) {
            return null;
        }

        $buttonId = intval($message);

        if ((string)$buttonId !== $message) {
            //$this->runtime->debug('NO button detected');
            return null;
        }

        $data = $this->stateStorage->read()->get($route->getChatId(), __METHOD__, []);
        $this->runtime->debug('button call', ['id' => $buttonId, 'message' => $message, 'route' => $route->getChatId(), 'data' => $data,]);
        return $data[TelegramTerminalOutput::LAST_BUTTON_COMMANDS][$buttonId] ?? null;
    }

    protected function _detectCommand(string $message): string
    {
        // cut message to detect command
        $message = substr(trim($message), 0, 256);

        // replace all white-space characters to detect white spaces;
        $message = strtr($message, ["\t" => ' ', "\n" => ' ', "\r" => ' ', "\0" => ' ', "\x0B" => ' ']);

        if (isset($message[0]) && $message[0] === '/') {
            $pos = strpos($message, ' ');
            if ($pos === false) {
                return $message;
            }

            return substr($message, 0, $pos);
        }

        return '';
    }

    private function _prepareUserId()
    {
        if (!$this->userId) {
            $this->userId = $this->context->getScope(RunContext::GLOBAL_CONFIG, 'USER_ID');
            if (!$this->userId) {
                $this->userId = mt_rand(1, 1000);
            }

            $msg = new ChannelMsg();
            $msg->body = 'UserId selected: ' . $this->userId . "\n" . 'To Change type !!!user_id=1';
            $msg->setChannelState(new ChannelState());
            $this->core->getDataChannel()->send($msg);
        }
    }

    protected function execProviderCommand(string $command): bool
    {
        $param = '';
        if (str_contains($command, ' ')) {
            [$command, $param] = explode(' ', $command, 2);
        }

        $acted = false;

        switch ($command) {
            case 'r' :  // reload
                exit(1);
            case 'u': // set user id in context
                $acted = true;
                $this->userId = intval($param);
                $this->runtime->debug('UserId is set', ['id' => $this->userId]);
                break;
        }

        return $acted;
    }

    /**
     * @return ?StorageProto
     */
    public function getStateStorage(): ?StorageProto
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