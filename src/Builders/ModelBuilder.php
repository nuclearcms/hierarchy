<?php

namespace Nuclear\Hierarchy\Builders;


use Illuminate\Support\Collection;
use Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\WriterContract;

class ModelBuilder implements ModelBuilderContract, WriterContract {

    use Writer;

    /**
     * Builds a source model
     *
     * @param string $name
     * @param Collection $fields
     */
    public function build($name, Collection $fields = null)
    {
        $path = $this->getClassFilePath($name);
        $tableName = source_table_name($name);

        $contents = view('_hierarchy::entities.model', [
            'tableName'        => $tableName,
            'name'             => $this->getClassName($name),
            'fields'           => $this->makeFields($fields),
            'searchableFields' => $this->makeSearchableFields($fields, $tableName),
            'mutatables'       => $this->makeMutatableFields($fields)
        ])->render();

        $this->write($path, $contents);
    }

    /**
     * Makes fields
     *
     * @param Collection $fields
     * @return string
     */
    protected function makeFields(Collection $fields = null)
    {
        if (is_null($fields))
        {
            return '';
        }

        $fields = $fields->pluck('name')->toArray();

        return count($fields) ? "'" . implode("', '", $fields) . "'" : '';
    }

    /**
     * Makes searchable fields
     *
     * @param Collection $fields
     * @param string $tableName
     * @return string
     */
    protected function makeSearchableFields(Collection $fields = null, $tableName)
    {
        if (is_null($fields))
        {
            return '';
        }

        $searchables = [];

        foreach ($fields as $field)
        {
            if (intval($field->search_priority) > 0)
            {
                $searchables[] = "'{$tableName}.{$field->name}' => {$field->search_priority}";
            }
        }

        return implode(",", $searchables);
    }

    /**
     * Makes mutatable fields
     *
     * @param Collection $fields
     * @return string
     */
    protected function makeMutatableFields(Collection $fields = null)
    {
        if (is_null($fields))
        {
            return '';
        }

        $mutatables = [];

        foreach ($fields as $field)
        {
            if (in_array($field->type, ['document', 'gallery', 'markdown', 'node', 'node_collection']))
            {
                $mutatables[] = "'{$field->name}' => '{$field->type}'";
            }
        }

        return implode(",", $mutatables);
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