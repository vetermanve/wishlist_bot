<?php


namespace Verse\Notify\Render;


use Verse\Notify\Spec\ChannelType;

class RenderFactory
{
    private $map = [
        ChannelType::TELEGRAM => TelegramRenderer::class
    ];

    public function getRendererByConnectionType($type) : ?NotifyRenderAbstract {
        if (isset($this->map[$type])) {
            return new $this->map[$type];
        }

        return null;
    }
}