<?php


namespace Verse\Telegram\Run\RequestRouter;


use Verse\Run\RunRequest;

interface TextRouterInterface
{
    /**
     * @param RunRequest $request
     * @return array [$class, $data]
     */
    public function getClassAndData(RunRequest $request);
}