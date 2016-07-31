<?php

use Nuclear\Hierarchy\Builders\MigrationBuilder;

if ( ! function_exists('generated_path'))
{
    /**
     * Get the path to the generated folder.
     *
     * @param string $path
     * @return string
     */
    function generated_path($path = '')
    {
        return app()->make('path.generated') . ($path ? '/' . $path : $path);
    }
}

if ( ! function_exists('source_model_name'))
{
    /**
     * Returns the name of the source model by key
     *
     * @param string $key
     * @param bool $withPath
     * @return string
     */
    function source_model_name($key, $withPath = false)
    {
        $name = studly_case(MigrationBuilder::TABLE_PREFIX . $key);

        return $withPath ? 'gen\\Entities\\' . $name : $name;
    }
}

if ( ! function_exists('source_form_name'))
{
    /**
     * Returns the name of the source form by key
     *
     * @param string $key
     * @return string
     */
    function source_form_name($key)
    {
        return 'Edit' . ucfirst($key) . 'Form';
    }
}

if ( ! function_exists('hierachy_bag'))
{
    /**
     * Returns a hierarchy bag
     *
     * @param string $bag
     * @return object
     */
    function hierarchy_bag($bag)
    {
        return app()->make('hierarchy.bags.' . $bag);
    }
}