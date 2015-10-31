<?php

use Nuclear\Hierarchy\Cache\Accessor;
use org\bovigo\vfs\vfsStream;

class AccessorTest extends TestBase {

    protected function getAccessor()
    {
        return new Accessor;
    }

    /** @test */
    function it_writes_to_cache()
    {
        $accessor = $this->getAccessor();

        $path = vfsStream::url('gen/fillables_cache.json');
        $array = [
            1 => ['area', 'location']
        ];

        $this->assertFileNotExists($path);

        $accessor->write($array);

        $this->assertFileExists($path);

        $this->assertStringEqualsFile(
            $path,
            json_encode($array)
        );
    }

    /** @test */
    function it_reads_the_cache()
    {
        $accessor = $this->getAccessor();

        $path = vfsStream::url('gen/fillables_cache.json');

        $array = [
            1 => ['area', 'location']
        ];

        file_put_contents($path, json_encode($array));

        $this->assertFileExists($path);

        $this->assertEquals(
            $array,
            $accessor->read()
        );
    }

    /** @test */
    function it_returns_the_field_for_node_type()
    {
        $accessor = $this->getAccessor();

        $path = vfsStream::url('gen/fillables_cache.json');

        $array = [
            1 => ['area', 'location']
        ];

        file_put_contents($path, json_encode($array));

        $this->assertFileExists($path);

        $this->assertEquals(
            $array[1],
            $accessor->getFieldsFor(1)
        );
    }

    /** @test */
    function it_checks_if_node_type_has_field()
    {
        $accessor = $this->getAccessor();

        $path = vfsStream::url('gen/fillables_cache.json');

        $array = [
            1 => ['area', 'location']
        ];

        file_put_contents($path, json_encode($array));

        $this->assertFileExists($path);

        $this->assertTrue(
            $accessor->nodeTypeHasField(1, 'area')
        );

        $this->assertFalse(
            $accessor->nodeTypeHasField(1, 'description')
        );
    }

}