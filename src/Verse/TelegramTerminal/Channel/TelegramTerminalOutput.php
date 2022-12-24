<?php


namespace Verse\TelegramTerminal\Channel;


use Verse\Run\ChannelMessage\ChannelMsg;
use Verse\Telegram\Run\Channel\SolidStateTelegramResponseChannel;
use Verse\Telegram\Run\Channel\Util\MessageRoute;
use Verse\Telegram\Run\Storage\UserStateStorage;

class TelegramTerminalOutput extends SolidStateTelegramResponseChannel
{
    public const LAST_BUTTON_COMMANDS = '__commands';

    /**
     * @var resource
     */
    protected $pointer;

    /**
     * @var UserStateStorage
     */
    private $stateStorage;

    public function prepare()
    {
        $this->pointer = fopen('php://stdout', 'w');
    }

    public function send(ChannelMsg $msg)
    {
        $out = '<<< ';
        $padding = '    ';
        $out .= str_replace("\n", "\n".$padding, is_string($msg->body) ? $msg->body : json_encode($msg->body));

//        $string = json_encode([
//            'code' => $msg->getCode(),
//            'data' => $msg->body,
//            'dest' => $msg->getDestination(),
//            'uid' => $msg->getUid(),
//            'meta' => $msg->getAllMeta(),
//        ], JSON_UNESCAPED_UNICODE);

        $idx = 0;
        $buttonCommands = [];
        foreach ($msg->getMeta('keyboard') ?? [] as $row) {
            $out .= "\n".$padding."[";

            foreach ($row as $bIdx => $button) {
                $idx++;
                $buttonCommands[$idx] = $button['callback_data'];
                $out .= ' ('.$idx.') '.$button['text'].' ';
                if ($bIdx != sizeof($row) - 1) {
                    $out .= '|';
                }
            }

            $out .= ']';
        }

        fwrite($this->pointer, $out . "\n");

        if (isset($this->stateStorage)) {
            $route = new MessageRoute($msg->getDestination());
            $data = (array)$msg->getChannelState()->pack(true);
            $data[self::LAST_BUTTON_COMMANDS] = $buttonCommands;
            //$this->runtime->debug('Write state storage', ['id' => $route->getChatId(), 'dest' => $msg->getDestination(), ]);
            $this->stateStorage->write()->update($route->getChatId(), $data, __METHOD__);
        }
    }

    /**
     * @return UserStateStorage
     */
    public function getStateStorage(): UserStateStorage
    {
        return $this->stateStorage;
    }

    /**
     * @param UserStateStorage $stateStorage
     */
    public function setStateStorage(UserStateStorage $stateStorage): void
    {
        $this->stateStorage = $stateStorage;
    }
}