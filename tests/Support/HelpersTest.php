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

}