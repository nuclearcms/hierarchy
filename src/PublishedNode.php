<?php

namespace Nuclear\Hierarchy;


class PublishedNode extends Node {

    /**
     * Boot the model
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new PublishedScope);
    }

    /**
     * Returns all published children with parameter ordered
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPublishedOrderedChildren($perPage = null)
    {
        return $this->getOrderedChildren($perPage);
    }

    /**
     * Returns all published children position ordered
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPublishedPositionOrderedChildren($perPage = null)
    {
        return $this->getPositionOrderedChildren($perPage);
    }

}