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
        'name', 'is_visible', 'hides_children', 'color', 'is_taggable', 'allowed_children_types'
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

	/**
	 * Content relation
	 *
	 * @return HasMany
	 */
	public function contents()
	{
		return $this->hasMany(Content::class);
	}

	/**
	 * Returns allowed children types
	 *
	 * @return Collection
	 */
	public function getAllowedChildrenTypes()
	{
		$types = collect($this->allowed_children_types)->pluck('id');

		return self::where('is_visible', true)->whereIn('id', $types)->get();
	}

}