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
    function it_registers_model_builder()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Builders\ModelBuilder',
            $this->app->make('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract')
        );
    }

    /** @test */
    function it_registers_migration_builder()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Builders\MigrationBuilder',
            $this->app->make('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract')
        );
    }

    /** @test */
    function it_registers_builder_service()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Builders\BuilderService',
            $this->app->make('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract')
        );
    }

    /** @test */
    function it_registers_helpers()
    {
        $this->assertTrue(
            function_exists('generated_path')
        );
    }

}