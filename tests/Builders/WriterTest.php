<?php

use org\bovigo\vfs\vfsStream;

class WriterTest extends TestBase {

    protected function getWriter()
    {
        return new WriterModel;
    }

    /** @test */
    function it_writes_a_file()
    {
        $writer = $this->getWriter();
        $path = vfsStream::url('gen/foo.txt');
        $content = 'foo';

        $this->assertFileNotExists($path);

        $writer->write($path, $content);

        $this->assertFileExists($path);
        $this->assertStringEqualsFile($path, $content);
    }

    /** @test */
    function it_deletes_a_file()
    {
        $writer = $this->getWriter();
        $path = vfsStream::url('gen/foo.txt');
        $content = 'foo';

        $writer->write($path, $content);

        $this->assertFileExists($path);

        $writer->delete($path, $content);

        $this->assertFileNotExists($path);
    }

}