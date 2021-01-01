<?php

if (!function_exists('flush_content_type_cache')) {

    /**
     * Flushes the content type cache
     *
     * @param int $id
     */
    function flush_content_type_cache($id)
    {
    	\Cache::forget('contentType.' . $id);
    	\Cache::forget('contentType.' . $id . '.rules');
    }

}