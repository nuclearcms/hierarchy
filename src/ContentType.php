<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class ContentType extends Model implements Searchable {

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'visible', 'hides_children', 'color', 'taggable', 'allowed_children_types'
    ];

    /**
	 * Casts
	 *
	 * @var array
	 */
	protected $casts = [
		'color' => 'array',
		'allowed_children_types' => 'array'
	];

	/**
	 * Searchable config
	 *
	 * @return SearchResult
	 */
	public function getSearchResult(): SearchResult
	{
		return new SearchResult($this, $this->name);
	}

	/**
	 * Content Field relation
	 *
	 * @return HasMany
	 */
	public function fields()
	{
		return $this->hasMany(ContentField::class);
	}

}