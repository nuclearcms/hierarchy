<?php

namespace Reactor\Tags;


use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kenarkose\Sortable\Sortable;
use Nicolaslopezj\Searchable\SearchableTrait;
use Nuclear\Hierarchy\Node;

class Tag extends Model {

    use Translatable, SearchableTrait;

    use Sortable
    {
        scopeSortable as _scopeSortable;
    }

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The fillable fields for the model.
     *
     * @var  array
     */
    protected $fillable = ['title', 'tag_name'];

    /**
     * Translatable
     */
    public $translatedAttributes = ['title', 'tag_name'];

    /**
     * Sortable columns
     *
     * @var array
     */
    protected $sortableColumns = ['title', 'created_at'];

    /**
     * Default sortable key
     *
     * @var string
     */
    protected $sortableKey = 'title';

    /**
     * Default sortable direction
     *
     * @var string
     */
    protected $sortableDirection = 'asc';

    /**
     * Searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'tag_translations.title'    => 10,
            'tag_translations.tag_name' => 10
        ],
        'joins'   => [
            'tag_translations' => ['tags.id', 'tag_translations.tag_id']
        ]
    ];

    /**
     * Node relation
     *
     * @return BelongsToMany
     */
    public function nodes()
    {
        return $this->belongsToMany(Node::class);
    }

    /**
     * Sortable by scope
     *
     * @param $query
     * @param string|null $key
     * @param string|null $direction
     * @return Builder
     */
    public function scopeSortable($query, $key = null, $direction = null)
    {
        list($key, $direction) = $this->validateSortableParameters($key, $direction);

        if ($this->isTranslationAttribute($key))
        {
            return $this->orderByTranslationAttribute($query, $key, $direction);
        }

        return $query->orderBy($key, $direction);
    }

    /**
     * @param Builder $query
     * @param $attribute
     * @param $direction
     * @return mixed
     */
    protected function orderByTranslationAttribute(Builder $query, $attribute, $direction)
    {
        $key = $this->getTable() . '.' . $this->getKeyName();

        return $query->join($this->getTranslationsTable() . ' as t', 't.tag_id', '=', $key)
            ->select('t.id as translation_id', 'tags.*')
            ->groupBy($key)
            ->orderBy('t.' . $attribute, $direction);
    }

    /**
     * Finds a tag by title or creates it
     *
     * @param string $title
     * @param string $locale
     * @return Tag
     */
    public static function firstByTitleOrCreate($title, $locale = null)
    {
        $tag = Tag::whereTranslation('title', $title, $locale)->first();

        if (is_null($tag))
        {
            $attributes = compact('title');

            if ($locale)
            {
                $attributes = [$locale => $attributes];
            }

            $tag = Tag::create($attributes);
        }

        return $tag;
    }

    /**
     * Returns locale for name
     *
     * @param string $name
     * @return string
     */
    public function getLocaleForName($name)
    {
        foreach ($this->translations as $translation)
        {
            if ($translation->tag_name === $name)
            {
                return $translation->locale;
            }
        }

        return null;
    }

    /**
     * Scope for selecting with tag name
     *
     * @param Builder $query
     * @param string $name
     * @return Builder
     */
    public function scopeWithName(Builder $query, $name)
    {
        return $this->scopeWhereTranslation($query, 'tag_name', $name);
    }

}
