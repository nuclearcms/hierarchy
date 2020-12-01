<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Bkwld\Cloner\Cloneable;

class ContentType extends Model implements Searchable {

	use Cloneable;

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_visible', 'hides_children', 'color', 'is_taggable', 'allowed_children_types'];

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
	 * Cloneable relations for duplication
	 *
	 * @var array
	 */
	protected $cloneable_relations = ['fields'];

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
		if(empty($this->allowed_children_types)) return [];
		
		return self::where('is_visible', true)->whereIn('id', $this->allowed_children_types)->orderByRaw('FIELD (id, ' . implode(', ', $this->allowed_children_types) . ') ASC')->get();
	}

	/**
	 * Modifier for duplication
	 *
	 * @param $source
	 * @param $child
	 */
	public function onCloning($source, $child)
	{
		$this->name .= ' [' . __('foundation::general.copy') . ']';
	}

}