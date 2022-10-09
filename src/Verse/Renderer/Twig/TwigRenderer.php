<?php


namespace Verse\Renderer\Twig;


use Verse\Renderer\RendererInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer implements RendererInterface
{
    private $_baseTemplatesPath = '';

    public function render(string $template, array $data = [], string $layout = 'main', array $templateDirectories = [])
    {
        $templateDirectories[] = $this->getBaseTemplatesPath();
        $fs = new FilesystemLoader($templateDirectories);
        $env = new Environment($fs);
        
        $data['_layout'] = $layout.'.twig';
        return $env->render($template.'.twig', $data);
    }
    
    /**
     * @return string
     */
    public function getBaseTemplatesPath(): string
    {
        return $this->_baseTemplatesPath;
    }

    /**
     * @param string $templatesPath
     */
    public function setBaseTemplatesPath(string $templatesPath): void
    {
        $this->_baseTemplatesPath = $templatesPath;
    }

}