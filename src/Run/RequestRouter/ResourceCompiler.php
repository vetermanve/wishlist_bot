<?php


namespace Run\RequestRouter;


class ResourceCompiler
{
    public static function fromClassName($className, $prefix = '/', $rootNamespace = 'App') {
        $className = strtr($className,[
            $rootNamespace.'\\' => '',
            'Controller\\' => '',
            "\\" => ''
        ]);

        return $prefix.strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $className));
    }
}