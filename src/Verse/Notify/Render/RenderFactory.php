<?php


namespace Verse\Notify\Render;


use Verse\Notify\Spec\ConnectionTypes;

class RenderFactory
{
    private $map = [
        ConnectionTypes::TELEGRAM => TelegramRenderer::class
    ];

    public function getRendererByConnectionType($type) : NotifyRenderAbstract {
        if (isset($this->map[$type])) {
            return new $this->map[$type];
        }

        return null;
    }
}