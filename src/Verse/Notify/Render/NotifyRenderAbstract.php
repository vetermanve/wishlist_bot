<?php


namespace Verse\Notify\Render;


abstract class NotifyRenderAbstract
{
    protected $meta = [];

    abstract public function getResult();

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

}