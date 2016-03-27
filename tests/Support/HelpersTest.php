<?php

class HelpersTest extends TestBase {

    /** @test */
    function it_registers_generated_path_helper()
    {
        $this->assertEquals(
            $this->app['path.generated'],
            generated_path()
        );
    }

    /** @test */
    function it_registers_source_model_name_helper()
    {
        $this->assertEquals(
            'NsProject',
            source_model_name('project')
        );
    }

    /** @test */
    function it_registers_source_form_name_helper()
    {
        $this->assertEquals(
            'EditProjectForm',
            source_form_name('project')
        );
    }

    /** @test */
    function it_registers_hierarchy_bag_helper()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Bags\NodeTypeBag',
            hierarchy_bag('nodetype')
        );
    }

}