<?php

namespace Nuclear\Hierarchy\Contract\Builders;


use Illuminate\Support\Collection;

interface FormBuilderContract {

    /**
     * Builds a source form
     *
     * @param string $name
     * @param Collection|null $fields
     */
    public function build($name, Collection $fields = null);

    /**
     * Destroys a source form
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