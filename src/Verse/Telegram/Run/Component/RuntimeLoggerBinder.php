<?php


namespace Verse\Telegram\Run\Component;


use Psr\Log\LoggerInterface;
use Verse\Di\Env;
use Verse\Run\Component\RunComponentProto;

class RuntimeLoggerBinder extends RunComponentProto
{

    public function run()
    {
        Env::getContainer()->setModule(LoggerInterface::class, $this->runtime);
    }
}