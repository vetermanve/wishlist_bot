<?php


namespace Verse\Renderer\Controller;


use Verse\Renderer\RendererInterface;
use ReflectionObject;
use Verse\Di\Env;

trait PageRenderTrait
{

    protected ?RendererInterface $_renderer = null;

    protected function getLayout() : string {
        return 'page';
    }

    public function getRenderer(): RendererInterface
    {
        if (!isset($this->_renderer)) {
            $this->_renderer = Env::getContainer()->bootstrap(RendererInterface::class);
        }

        return $this->_renderer;
    }


    protected function _getRenderDefaultData() : array {
        return [];
//        return [
//            '_userId' => $this->_userId,
//            '_pages' => $this->_pages(),
//            '_currentPage' => $this->requestWrapper->getResource(),
//        ];
    }

    protected function getRendererTemplatesPath() : string {
        $object = new ReflectionObject($this);
        $dir = dirname($object->getFileName());
        return $dir. '/../Template';
    }

    protected function _render($template, $data = [])
    {
        $data += $this->_getRenderDefaultData();

        $page = $this->getRenderer()->render($template, $data,
            $this->getLayout(),
            [
                $this->getRendererTemplatesPath()
            ]
        );

        return preg_replace('/\n\s+/', "\n", $page);
    }
}