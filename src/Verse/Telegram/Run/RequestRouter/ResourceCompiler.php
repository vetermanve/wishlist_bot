<?php


namespace Verse\Telegram\Run\RequestRouter;


class ResourceCompiler
{
    public static function fromClassName($className, $prefix = '/', $rootNamespace = 'App') {
        $className = strtr($className,[
            $rootNamespace.'\\' => '',
            'Controller\\' => '',
            "\\" => ''
        ]);

        $text = strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $className));
        $parts = explode('_', $text);
        if ($parts[0] === $parts[1]) {
            return $prefix.$parts[0];
        }

        return $prefix.$text;
    }
}