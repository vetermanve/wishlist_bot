<?php


namespace Verse\Scheduler\Mock;


use Verse\Run\Processor\RunRequestProcessorProto;
use Verse\Run\Provider\RequestProviderProto;
use Verse\Run\RunRequest;

class MockProcessor extends RunRequestProcessorProto
{
    private ?RunRequest $lastRequest;

    public function prepare()
    {
        $this->lastRequest = null;
    }

    public function process(RunRequest $request)
    {
        $this->lastRequest = $request;
    }

    /**
     * @return RunRequest
     */
    public function getLastRequest(): RunRequest
    {
        return $this->lastRequest;
    }
}