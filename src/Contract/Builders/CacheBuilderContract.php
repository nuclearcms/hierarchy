<?php

namespace Nuclear\Hierarchy\Contract\Builders;


interface CacheBuilderContract {

    /**
     * Builds the cache
     *
     * @param int $id
     * @param array $fields
     */
    public function build($id, array $fields);

    /**
     * Destroys the cache
     *
     * @param int $id
     */
    public function destroy($id);

}