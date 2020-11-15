<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;

class ContentField extends Model {

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'label', 'description', 'position', 'type', 'visible', 'search_priority',
        'default_value', 'rules', 'options',
    ];

    /**
     * Content Type relation
     *
     * @return BelongsTo
     */
    public function contentType()
    {
    	return $this->belongsTo(ContentType::class);
    }

}