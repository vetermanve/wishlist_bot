<?php


namespace Verse\Renderer;


interface RendererInterface
{
    public function getBaseTemplatesPath() : string;
    public function setBaseTemplatesPath(string $templatesPath) : void;
    public function render (string $template, array $data = [], string $layout = 'main', array $templateDirectories = []);
}