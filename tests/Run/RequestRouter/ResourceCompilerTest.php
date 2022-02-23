<?php


namespace Run\RequestRouter;


use App\Done\Controller\Done;
use App\Item\Controller\All;
use App\Item\Controller\EditMode;
use App\Landing\Controller\Landing;
use PHPUnit\Framework\TestCase;

class ResourceCompilerTest extends TestCase
{
    public function testTrue()
    {
        $cases = [
            EditMode::class => '/item_edit_mode',
            All::class => '/item_all',
            Done::class => '/done_done',
            Landing::class => '/landing_landing',
            '\\App\\Test\\Controller\\SomeVeryName' => '/test_some_very_name',
            '\\More\\Test\\Controller\\SomeVeryName' => '/more_test_some_very_name'
        ];

        $all = new All();

        foreach ($cases as $class => $shouldBeResource) {
            $this->assertEquals($shouldBeResource, ResourceCompiler::fromClassName($class));
        }
    }
}