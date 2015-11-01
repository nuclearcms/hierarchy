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
     * @return string
     */
    function source_model_name($key)
    {
        return studly_case(MigrationBuilder::TABLE_PREFIX . $key);
    }
}