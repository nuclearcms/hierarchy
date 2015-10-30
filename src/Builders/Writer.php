<?php

namespace Nuclear\Hierarchy\Builders;


use Nuclear\Hierarchy\Support\FileKeeper;

trait Writer {

    /**
     * Writes a file, also creates base directory
     *
     * @param $path
     * @param $contents
     */
    public function write($path, $contents)
    {
        if ( ! FileKeeper::exists($dir = $this->getBasePath()))
        {
            FileKeeper::directory($dir);
        }

        FileKeeper::write($path, $contents);
    }

    /**
     * Deletes a field
     *
     * @param string $path
     */
    public function delete($path)
    {
        FileKeeper::delete($path);
    }

}