<?php

use Nuclear\Hierarchy\Builders\CacheBuilder;

class CacheBuilderTest extends TestBase {

    protected function getBuilder()
    {
        return new CacheBuilder;
    }

    /** @test */
    function it_builds_the_cache()
    {
        $accessor = $this->app['hierarchy.cache'];
        $builder = $this->getBuilder();

        $accessor->write([]);

        $this->assertEquals(
            $accessor->read(),
            []
        );

        $builder->build(1, ['area', 'location']);

        $this->assertEquals(
            $accessor->read(),
            [1 => ['area', 'location']]
        );
    }

    /** @test */
    function it_destroys_the_cache()
    {
        $accessor = $this->app['hierarchy.cache'];
        $builder = $this->getBuilder();

        $accessor->write([1 => ['area', 'description']]);

        $this->assertEquals(
            $accessor->read(),
            [1 => ['area', 'description']]
        );

        $builder->destroy(1);

        $this->assertEquals(
            $accessor->read(),
            []
        );
    }

}