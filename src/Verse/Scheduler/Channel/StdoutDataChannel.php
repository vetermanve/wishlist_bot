<?php


namespace Verse\Scheduler\Channel;


use Verse\Run\Channel\DataChannelProto;
use Verse\Run\ChannelMessage\ChannelMsg;

class StdoutDataChannel extends DataChannelProto
{
    /**
     * @var resource
     */
    protected $pointer;

    public function prepare()
    {
        $this->pointer = fopen('php://stdout', 'w');
    }

    public function send(ChannelMsg $msg)
    {
        $string = json_encode([
            'code' => $msg->getCode(),
            'data' => $msg->body,
            'dest' => $msg->getDestination(),
            'uid'  => $msg->getUid(),
            'meta' => $msg->getAllMeta(),
        ], JSON_UNESCAPED_UNICODE);
        fwrite($this->pointer, $string . "\n");
    }
}