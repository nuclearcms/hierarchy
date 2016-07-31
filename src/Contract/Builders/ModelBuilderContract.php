<?php

namespace Nuclear\Hierarchy\Contract\Builders;


use Illuminate\Support\Collection;

interface ModelBuilderContract {

    /**
     * Builds a source model
     *
     * @param string $name
     * @param Collection $fields
     */
    public function build($name, Collection $fields = null);

    /**
     * Destroys a source model
     *
     * @param string $name
     */
    public function destroy($name);

    /**
     * Creates a class name from table name
     *
     * @param string $name
     * @return string
     */
    public function getClassName($name);

    /**
     * Gets the class file path for given name
     *
     * @param string $name
     * @return string
     */
    public function getClassFilePath($name);

}