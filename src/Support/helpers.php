<?php

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