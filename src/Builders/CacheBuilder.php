<?php

namespace Nuclear\Hierarchy\Builders;


use Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract;

class CacheBuilder implements CacheBuilderContract {

    /**
     * Builds the cache
     *
     * @param int $id
     * @param array $fields
     */
    public function build($id, array $fields)
    {
        $cache = $this->getCache()->read();

        $cache[$id] = $fields;

        $this->getCache()->write($cache);
    }

    /**
     * Destroys the cache
     *
     * @param int $id
     */
    public function destroy($id)
    {
        $cache = $this->getCache()->read();

        unset($cache[$id]);

        $this->getCache()->write($cache);
    }

    /**
     * Getter for cache
     *
     * @return CacheAccessor
     */
    public function getCache()
    {
        return app('hierarchy.cache');
    }

}