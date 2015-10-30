<?php

namespace Nuclear\Hierarchy\Contract\Builders;


interface WriterContract {

    /**
     * Getter for entity base path
     *
     * @return string
     */
    public function getBasePath();

    /**
     * Writes a file, also creates base directory
     *
     * @param $path
     * @param $contents
     */
    public function write($path, $contents);

    /**
     * Deletes a field
     *
     * @param string $path
     */
    public function delete($path);

}