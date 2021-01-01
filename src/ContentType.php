<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Bkwld\Cloner\Cloneable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ContentType extends Model implements Searchable {

	use Cloneable, Cachable;

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
	 * @param bool $visibleOnly
	 * @return Collection
	 */
	public function getAllowedChildrenTypes($visibleOnly = false)
	{
		if(empty($this->allowed_children_types)) return [];

		$q = self::whereIn('id', $this->allowed_children_types)->orderByRaw('FIELD (id, ' . implode(', ', $this->allowed_children_types) . ') ASC');

		if($visibleOnly) $q = $q->where('is_visible', true);
		
		return $q->get();
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