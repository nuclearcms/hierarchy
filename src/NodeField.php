<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Model as Eloquent;

class NodeField extends Eloquent {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'label', 'description', 'position', 'type'];

    /**
     * Parent node type relation
     *
     * @return BelongsTo
     */
    public function nodeType()
    {
        return $this->belongsTo(NodeType::class);
    }

    /**
     * Getter for field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Getter for field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}