<?php

namespace Nuclear\Hierarchy;


use Illuminate\Pagination\LengthAwarePaginator;
use Kalnoy\Nestedset\Node as BaseNode;
use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Node extends BaseNode {

    /**
     * The translatable trait requires some modification
     */
    use Translatable
    {
        isTranslationAttribute as _isTranslationAttribute;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'node_name',
        'meta_title', 'meta_keywords', 'meta_description',
        'visible', 'sterile', 'home', 'locked', 'status', 'hides_children', 'priority',
        'published_at', 'children_order', 'children_order_direction'];

    /**
     * The translated fields for the model.
     */
    protected $translatedAttributes = ['title', 'node_name',
        'meta_title', 'meta_keywords', 'meta_description'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * The translation model is the NodeSource for use
     * and the table name
     *
     * @var string
     */
    protected $translationModel = 'Nuclear\Hierarchy\NodeSource';
    protected $sourcesTable = 'node_sources';

    /**
     * The locale key
     *
     * @var string
     */
    protected $localeKey = 'locale';

    /**
     * The node type key
     *
     * @var string
     */
    protected $nodeTypeKey = 'node_type_id';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Status codes
     *
     * @var int
     */
    const DRAFT = 30;
    const PENDING = 40;
    const PUBLISHED = 50;
    const ARCHIVED = 60;

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($node)
        {
            $node->published_at = Carbon::now();
        });
    }

    /**
     * The node type relation
     *
     * @return BelongsTo
     */
    public function nodeType()
    {
        return $this->belongsTo(NodeType::class);
    }

    /**
     * Gets the node type key
     *
     * @return int $id
     */
    public function getNodeTypeKey()
    {
        return $this->getAttribute($this->nodeTypeKey);
    }

    /**
     * Sets the node type key
     *
     * @param int $id
     */
    public function setNodeTypeKey($id)
    {
        $this->setAttribute($this->nodeTypeKey, $id);
    }

    /**
     * Sets the node type by key and validates it
     *
     * @param int $id
     * @return NodeType
     */
    public function setNodeTypeByKey($id)
    {
        $this->nodeType()->associate(
            NodeType::findOrFail($id)
        );
    }

    /**
     * Checks if key is a translation attribute
     *
     * @param string $key
     * @return bool
     */
    public function isTranslationAttribute($key)
    {
        if ($this->isSpecialAttribute($key))
        {
            return false;
        }

        return $this->_isTranslationAttribute($key) || $this->isCachedAttribute($key);
    }

    /**
     * Checks if the given key is a special attribute
     * (These keys requires special protection)
     *
     * @param $key
     * @return bool
     */
    protected function isSpecialAttribute($key)
    {
        return in_array($key, [
            $this->nodeTypeKey,
            $this->getKeyName(),
            'translationForeignKey'
        ]);
    }

    /**
     * Checks if a key is a cached node source attribute
     *
     * @param $key
     * @return bool
     */
    protected function isCachedAttribute($key)
    {
        return app('hierarchy.cache')->nodeTypeHasField(
            $this->getNodeTypeKey(), $key
        );
    }

    /**
     * Checks if the translation is dirty
     *
     * @param \Illuminate\Database\Eloquent\Model $translation
     * @return bool
     */
    protected function isTranslationDirty(Model $translation)
    {
        return $translation->isDirty();
    }

    /**
     * Determine if the given attribute may be mass assigned.
     * (This method is an extension to the base Model isFillable method.
     * It includes the cached attributes in order to check if keys are fillable.)
     *
     * @param  string $key
     * @return bool
     */
    public function isFillable($key)
    {
        // We can assume cached attributes are fillable
        if ($this->isCachedAttribute($key))
        {
            return true;
        }

        return parent::isFillable($key);
    }

    /**
     * Overloading default Translatable functionality for
     * creating a new translation
     *
     * @param string $locale
     * @return Model
     */
    public function getNewTranslation($locale)
    {
        $nodeSource = NodeSource::newWithType(
            $locale,
            $this->nodeType->name
        );

        $this->translations->add($nodeSource);

        return $nodeSource;
    }

    /**
     * Returns a translation attribute
     * (optionally with fallback)
     *
     * @param string $key
     * @param string $locale
     * @param bool $fallback
     * @return string|null
     */
    public function getTranslationAttribute($key, $locale = null, $fallback = true)
    {
        if ($this->isTranslationAttribute($key))
        {
            $locale = $locale ?: $this->locale();

            $translation = $this->translate($locale);

            $attribute = ($translation) ? $translation->$key : null;

            if (empty($attribute) && $fallback)
            {
                $attribute = $this->translate($this->getFallbackLocale())->$key;
            }

            return $attribute;
        }

        return null;
    }

    /**
     * Sorts by source attribute
     *
     * @param Builder $query
     * @param string $attribute
     * @param string $direction
     * @return Builder
     */
    public function scopeSortedBySourceAttribute(Builder $query, $attribute, $direction = 'ASC', $locale = null)
    {
        $key = $this->getTable() . '.' . $this->getKey();

        return $query->join($this->sourcesTable . ' as t', 't.node_id', '=', $key)
            ->where($this->getLocaleKey(), ($locale) ?: $this->locale())
            ->groupBy($key)
            ->orderBy('t.' . $attribute, $direction)
            ->with('translations');
    }

    /**
     * Published scope
     *
     * @param Builder $query
     */
    public function scopePublished($query)
    {
        return $query->where(function ($query)
        {
            $query->where('status', '>=', Node::PUBLISHED)
                ->orWhere(function ($query)
                {
                    $query->where('status', '>=', Node::PENDING)
                        ->where('published_at', '<=', Carbon::now());
                });
        });
    }

    /**
     * Not published scope
     *
     * @param Builder $query
     */
    public function scopeNotPublished($query)
    {
        return $query->where(function ($query)
        {
            $query->where('status', '<=', Node::DRAFT)
                ->orWhere(function ($query)
                {
                    $query->where('status', '<=', Node::PENDING)
                        ->where('published_at', '>', Carbon::now());
                });
        });
    }

    /**
     * Draft scope
     *
     * @param Builder $query
     */
    public function scopeDraft($query)
    {
        return $query->where('status', Node::PENDING);
    }

    /**
     * Pending scope
     *
     * @param Builder $query
     */
    public function scopePending($query)
    {
        return $query->where('status', Node::PENDING)
            ->where('published_at', '>', Carbon::now());
    }

    /**
     * Archived scope
     *
     * @param Builder $query
     */
    public function scopeArchived($query)
    {
        return $query->where('status', Node::ARCHIVED);
    }


    /**
     * Scope invisible
     *
     * @param Builder $query
     */
    public function scopeInvisible($query)
    {
        return $query->whereVisible(0);
    }

    /**
     * Scope locked
     *
     * @param Builder $query
     */
    public function scopeLocked($query)
    {
        return $query->whereLocked(1);
    }

    /**
     * Children accessor
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get ordered children
     *
     * @param int|null $perPage
     * @param string|null $locale
     * @return Collection|LengthAwarePaginator
     */
    public function getOrderedChildren($perPage = null, $locale = null)
    {
        $children = $this->children();

        $this->determineChildrenSorting($children, $locale);

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Returns all published children with parameter ordered
     *
     * @param int|null $perPage
     * @param string|null $locale
     * @return Collection|LengthAwarePaginator
     */
    public function getPublishedOrderedChildren($perPage = null, $locale = null)
    {
        $children = $this->children()
            ->published();

        $this->determineChildrenSorting($children, $locale);

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Determines the children sorting
     *
     * @param $children
     * @param $locale
     */
    public function determineChildrenSorting($children, $locale)
    {
        if (in_array($this->children_order, $this->translatedAttributes))
        {
            $children->sortedBySourceAttribute(
                $this->children_order,
                $this->children_order_direction,
                $locale
            );
        } else
        {
            $children->orderBy(
                $this->children_order, $this->children_order_direction
            );
        };
    }

    /**
     * Determines the pagination of children
     *
     * @param $perPage
     * @param $children
     * @return mixed
     */
    public function determineChildrenPagination($perPage, $children)
    {
        return is_null($perPage) ?
            $children->get() :
            $children->paginate($perPage);
    }

    /**
     * Returns all children ordered by position
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPositionOrderedChildren($perPage = null)
    {
        $children = $this->children()
            ->defaultOrder();

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Returns all published children position ordered
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPublishedPositionOrderedChildren($perPage = null)
    {
        $children = $this->children()
            ->published()
            ->defaultOrder();

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Filters children by locale
     *
     * @param string $locale
     * @return Collection
     */
    public function hasTranslatedChildren($locale)
    {
        $children = $this->getChildren()->filter(function ($item) use ($locale)
        {
            return $item->hasTranslation($locale);
        });

        return (count($children) > 0);
    }

    /**
     * Deletes a translation
     *
     * @param string $locale
     * @return bool
     */
    public function deleteTranslation($locale)
    {
        if ($this->hasTranslation($locale))
        {
            if ($deleted = $this->getTranslation($locale)->delete())
            {
                $this->load('translations');

                return true;
            }
        }

        return false;
    }

    /**
     * Returns locale for name
     *
     * @param string $name
     * @return string
     */
    public function getLocaleForNodeName($name)
    {
        foreach ($this->translations as $translation)
        {
            if ($translation->node_name === $name)
            {
                return $translation->locale;
            }
        }

        return null;
    }

    /**
     * Sets the node status to published
     *
     * @return $this
     */
    public function publish()
    {
        $this->status = Node::PUBLISHED;

        return $this;
    }

    /**
     * Sets the node status to unpublished
     *
     * @return $this
     */
    public function unpublish()
    {
        $this->status = Node::DRAFT;

        return $this;
    }

    /**
     * Sets the node status to archived
     *
     * @return $this
     */
    public function archive()
    {
        $this->status = Node::ARCHIVED;

        return $this;
    }

    /**
     * Checks if node hides children
     *
     * @return bool
     */
    public function hidesChildren()
    {
        return $this->hides_children || $this->nodeType->hides_children;
    }

    /**
     * Checks if node can have children
     *
     * @return bool
     */
    public function canHaveChildren()
    {
        return ! (bool)$this->sterile;
    }

    /**
     * Checks if a node is published
     *
     * @return bool
     */
    public function isPublished()
    {
        return ($this->status >= Node::PUBLISHED)
        || ($this->status >= Node::PENDING && $this->published_at <= Carbon::now());
    }

    /**
     * Transforms the node type with to given type
     *
     * @param int $id
     * @throws \RuntimeException
     */
    public function transformInto($id)
    {
        $newType = NodeType::find($id);

        if (is_null($newType))
        {
            throw new \RuntimeException('Node type does not exist');
        }

        $sourceAttributes = $this->parseSourceAttributes();

        $this->flushSources();

        $this->transformNodeType($newType);

        $this->remakeSources($newType);

        $this->fill($sourceAttributes);

        $this->save();
    }

    /**
     * Parses source attributes
     *
     * @return array
     */
    public function parseSourceAttributes()
    {
        $attributes = [];

        foreach ($this->translations as $translation)
        {
            $attributes[$translation->locale] = $translation->source->toArray();
        }

        return $attributes;
    }

    /**
     * Flushes the source attributes
     */
    protected function flushSources()
    {
        foreach ($this->translations as $translation)
        {
            $translation->source->delete();
            $translation->flushTemporarySource();

            unset($translation->relations['source']);
        }
    }

    /**
     * Transforms the node type
     *
     * @param NodeType $nodeType
     */
    protected function transformNodeType(NodeType $nodeType)
    {
        $this->setNodeTypeByKey($nodeType->getKey());

        foreach ($this->translations as $translation)
        {
            $translation->source_type = $nodeType->name;
        }
    }

    /**
     * Remakes sources
     */
    protected function remakeSources(NodeType $nodeType)
    {
        foreach ($this->translations as $translation)
        {
            $source = $translation->getNewSourceModel($nodeType->name);
            $source->id = $translation->getKey();

            $translation->relations['source'] = $source;
        }
    }

}