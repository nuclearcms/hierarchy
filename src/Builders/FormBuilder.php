<?php

namespace Nuclear\Hierarchy\Builders;


use Illuminate\Support\Collection;
use Nuclear\Hierarchy\Contract\Builders\FormBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\WriterContract;

class FormBuilder implements FormBuilderContract, WriterContract {

    use Writer;

    /**
     * Builds a source form
     *
     * @param string $name
     * @param Collection|null $fields
     */
    public function build($name, Collection $fields = null)
    {
        // TODO: Implement build() method.
    }

    /**
     * Destroys a source form
     *
     * @param string $name
     */
    public function destroy($name)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * Creates a class name from table name
     *
     * @param string $name
     * @return string
     */
    public function getClassName($name)
    {
        return source_form_name($name);
    }

    /**
     * Getter for entity base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return generated_path('Forms');
    }

    /**
     * Gets the class file path for given name
     *
     * @param string $name
     * @return string
     */
    public function getClassFilePath($name)
    {
        return $this->getBasePath() . '/' .
        $this->getClassName($name) .
        '.php';
    }

}