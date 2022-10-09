<?php

namespace Verse\Renderer\Component;

use Verse\Di\Env;
use Verse\Renderer\RendererInterface;
use Verse\Renderer\Twig\TwigRenderer;
use Verse\Run\Component\RunComponentProto;

class RenderSetupComponent extends RunComponentProto
{

    public function run()
    {
        Env::getContainer()->setModule(RendererInterface::class, function (){
            $renderer = new TwigRenderer();
            $renderer->setBaseTemplatesPath(getcwd().'/src/App/Base/Templates');
            return $renderer;
        });
    }
}