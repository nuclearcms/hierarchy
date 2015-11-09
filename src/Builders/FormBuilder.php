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
        $path = $this->getClassFilePath($name);

        $contents = view('_hierarchy::entities.form', [
            'name'   => $this->getClassName($name),
            'fields' => $fields ?: []
        ])->render();

        $this->write($path, $contents);
    }

    /**
     * Destroys a source form
     *
     * @param string $name
     */
    public function destroy($name)
    {
        $path = $this->getClassFilePath($name);

        $this->delete($path);
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