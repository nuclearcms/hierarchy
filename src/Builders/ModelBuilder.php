<?php

namespace Nuclear\Hierarchy\Builders;


use Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\WriterContract;

class ModelBuilder implements ModelBuilderContract, WriterContract {

    use Writer;

    /**
     * Builds a source model
     *
     * @param string $name
     * @param array $fields
     */
    public function build($name, array $fields)
    {
        $path = $this->getClassFilePath($name);

        $contents = view('_hierarchy::entities.model', [
            'name'   => $this->getClassName($name),
            'fields' => count($fields) ? "'" . implode("', '", $fields) . "'" : ''
        ])->render();

        $this->write($path, $contents);
    }

    /**
     * Destroys a source model
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
        return source_model_name($name);
    }

    /**
     * Getter for entity base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return generated_path('Entities');
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