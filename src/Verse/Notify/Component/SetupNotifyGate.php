<?php


namespace Verse\Notify\Component;


use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Run\Component\RunComponentProto;

class SetupNotifyGate extends RunComponentProto
{
    public function run()
    {
        Env::getContainer()->setModule(NotifyGate::class, function () {
            return new NotifyGate();
        });
    }
}