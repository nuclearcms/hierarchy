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
    function it_registers_source_model_name_helpers()
    {
        $this->assertEquals(
            'NsProject',
            source_model_name('project')
        );
    }

    /** @test */
    function it_registers_source_form_name_helpers()
    {
        $this->assertEquals(
            'CreateEditProjectForm',
            source_form_name('project')
        );
    }

}