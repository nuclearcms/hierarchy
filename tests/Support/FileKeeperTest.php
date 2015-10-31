<?php

use Nuclear\Hierarchy\Support\FileKeeper;
use org\bovigo\vfs\vfsStream;

class FileKeeperTest extends TestBase {

    /** @test */
    function it_reads_a_file()
    {
        $path = vfsStream::url('gen/text.txt');

        FileKeeper::write($path, 'foo');

        $this->assertFileExists($path);

        $this->assertEquals(
            FileKeeper::read($path),
            'foo'
        );
    }

    /** @test */
    function it_writes_a_file()
    {
        $path = vfsStream::url('gen/text.txt');

        FileKeeper::write($path, 'foo');

        $this->assertFileExists($path);
        $this->assertStringEqualsFile($path, 'foo');
    }

    /** @test */
    function it_checks_if_file_or_directory_exists()
    {
        $base = vfsStream::url('gen');

        $this->assertFalse(FileKeeper::exists($base . '/dir'));
        $this->assertFalse(FileKeeper::exists($base . '/foo.txt'));

        FileKeeper::directory($base . '/dir');
        FileKeeper::write($base . '/foo.txt', 'foo');

        $this->assertTrue(FileKeeper::exists($base . '/dir'));
        $this->assertTrue(FileKeeper::exists($base . '/foo.txt'));
    }

    /** @test */
    function it_creates_directories()
    {
        $base = vfsStream::url('gen');

        $this->assertFileNotExists($base . '/dir');

        FileKeeper::directory($base . '/dir');

        $this->assertFileExists($base . '/dir');

        $this->assertFileNotExists($base . '/re/cur/sive');

        FileKeeper::directory($base . '/re/cur/sive');

        $this->assertFileExists($base . '/re/cur/sive');
    }

    /** @test */
    function it_deletes_a_file()
    {
        $path = vfsStream::url('gen/text.txt');

        FileKeeper::write($path, 'foo');

        $this->assertFileExists($path);

        FileKeeper::delete($path);

        $this->assertFileNotExists($path);
    }

}