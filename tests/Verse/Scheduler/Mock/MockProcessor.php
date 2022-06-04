<?php


namespace Verse\Scheduler\Mock;


use Verse\Run\Processor\RunRequestProcessorProto;
use Verse\Run\Provider\RequestProviderProto;
use Verse\Run\RunRequest;

class MockProcessor extends RunRequestProcessorProto
{
    private ?RunRequest $lastRequest;
    private array $allRequests = [];

    public function prepare()
    {
        $this->lastRequest = null;
    }

    public function process(RunRequest $request)
    {
        $this->runtime->info("Run request: ", ['route' => $request->getResource(), ]);
        $this->lastRequest = $request;
        $this->allRequests[] = $request;
    }

    /**
     * @return RunRequest
     */
    public function getLastRequest(): RunRequest
    {
        return $this->lastRequest;
    }

    /**
     * @return array
     */
    public function getAllRequests(): array
    {
        return $this->allRequests;
    }
}