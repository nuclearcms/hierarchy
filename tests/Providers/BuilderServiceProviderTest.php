<?php

class BuilderServiceProviderTest {

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
    function it_registers_form_builder()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Builders\FormBuilder',
            $this->app->make('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract')
        );
    }

    /** @test */
    function it_registers_cache_builder()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Builders\CacheBuilder',
            $this->app->make('Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract')
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

}