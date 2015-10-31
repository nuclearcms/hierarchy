<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Model as Eloquent;

class NodeSource extends Eloquent {

    /**
     * The fillable fields for the model.
     */
    protected $fillable = ['title', 'node_name'];

    /*
     * Timestamps for the model.
     */
    public $timestamps = false;

    /**
     * Node relationship
     *
     * @return BelongsTo
     */
    public function node()
    {
        return $this->belongsTo($this->getNodeModelName());
    }

    /**
     * Getter for node model name
     *
     * @return string
     */
    protected function getNodeModelName()
    {
        return $this->nodeModelName ?: 'Nuclear\Hierarchy\Node';
    }

}