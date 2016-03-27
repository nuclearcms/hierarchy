<?php

use org\bovigo\vfs\vfsStream;

class HierarchyServiceProviderTest extends TestBase {

    /** @test */
    function it_registers_generated_path()
    {
        $this->assertStringStartsWith(vfsStream::url('gen'), app('path.generated'));
        $this->assertInternalType('string', app('path.generated'));
    }

    /** @test */
    function it_registers_cache_accessor()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Cache\Accessor',
            $this->app['hierarchy.cache']
        );
    }

    /** @test */
    function it_registers_node_type_bag()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Bags\NodeTypeBag',
            $this->app['hierarchy.bags.nodetype']
        );
    }

}