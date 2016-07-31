<?php

namespace Nuclear\Hierarchy\Tags;


use Nuclear\Hierarchy\Tags\Tag;

trait Taggable {

    /**
     * Checks if node is taggable
     *
     * @return bool
     */
    public function isTaggable()
    {
        return (bool)$this->getNodeType()->taggable;
    }


    /**
     * Tag relation
     *
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'node_tag', 'node_id', 'tag_id');
    }

    /**
     * Links a tag to the node
     *
     * @param int $id
     */
    public function attachTag($id)
    {
        if ( ! $this->tags->contains($id))
        {
            return $this->tags()->attach($id);
        }
    }

    /**
     * Unlinks a tag from the node
     *
     * @param int $id
     */
    public function detachTag($id)
    {
        return $this->tags()->detach($id);
    }

}