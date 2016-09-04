<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\NodeTrait;
use Kenarkose\Chronicle\RecordsActivity;
use Kenarkose\Ownable\AutoAssociatesOwner;
use Kenarkose\Ownable\Ownable;
use Kenarkose\Sortable\Sortable;
use Kenarkose\Tracker\Trackable;
use Kenarkose\Tracker\TrackableInterface;
use Nuclear\Hierarchy\Exception\InvalidParentNodeTypeException;
use Nuclear\Hierarchy\Tags\Taggable;

class Node extends Eloquent implements TrackableInterface {

    use NodeTrait, Taggable, Searchable, Ownable,
        AutoAssociatesOwner, RecordsActivity, Trackable;

    use Sortable
    {
        scopeSortable as _scopeSortable;
    }

    /**
     * The translatable trait requires some modification
     */
    use Translatable
    {
        isTranslationAttribute as _isTranslationAttribute;
    }

    /**
     * Table for the model
     *
     * We hardcode this since we would like to keep
     * the child classes in the same table
     */
    protected $table = 'nodes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'node_name',
        'meta_title', 'meta_keywords', 'meta_description', 'meta_image', 'meta_author',
        'visible', 'sterile', 'home', 'locked', 'status', 'hides_children', 'priority',
        'published_at', 'children_order', 'children_order_direction', 'children_display_mode'];

    /**
     * The translated fields for the model.
     */
    protected $translatedAttributes = ['title', 'node_name',
        'meta_title', 'meta_keywords', 'meta_description', 'meta_image', 'meta_author'];

    /**
     * Searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'node_sources.title'         => 50,
            'node_sources.meta_keywords' => 20
        ],
        'joins'   => [
            'node_sources' => ['nodes.id', 'node_sources.node_id'],
        ]
    ];

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
    protected $sortableKey = 'created_at';

    /**
     * Default sortable direction
     *
     * @var string
     */
    protected $sortableDirection = 'desc';

    /**
     * Tracker relation configuration
     *
     * We are being explicit here to be able to extend
     * with different models
     */
    protected $trackerPivotTable = 'node_site_view';
    protected $trackerForeignKey = 'node_id';

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
     * Translation foreign key
     *
     * @var string
     */
    protected $translationForeignKey = 'node_id';

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
     * Node type name cache
     *
     * @var string
     */
    protected $nodeTypeName = null;

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

        static::addGlobalScope(new MailingScope);

        static::creating(function ($node)
        {
            if (empty($node->published_at))
            {
                $node->published_at = Carbon::now();
            }

            $node->fireNodeEvent('creating');
        });

        static::created(function ($node)
        {
            $node->propagateIdToSources(false);

            $node->fireNodeEvent('created');
        });

        static::saving(function ($node)
        {
            $node->validateCanHaveParentOfType();

            $node->fireNodeEvent('saving');
        });

        foreach (['updating', 'updated', 'deleting', 'deleted', 'saved'] as $event)
        {
            static::$event(function ($node) use ($event)
            {
                $node->fireNodeEvent($event);
            });
        }
    }

    /**
     * Fires a node event
     *
     * @param string $event
     */
    public function fireNodeEvent($event)
    {
        event($this->getNodeTypeName() . '.' . $event, $this);
    }

    /**
     * Propagates self id to sources
     *
     * The save parameter is to prevent saving sources prematurely
     * @param bool $save
     */
    public function propagateIdToSources($save = true)
    {
        foreach ($this->translations as $source)
        {
            $source->setExtensionNodeId($this->getKey(), $save);
        }
    }


    /**
     * Validates parent type
     */
    public function validateCanHaveParentOfType()
    {
        if (is_null($this->parent))
        {
            return;
        }

        $allowedNodeTypes = json_decode($this->parent->getNodeType()->allowed_children);

        if (empty($allowedNodeTypes) || in_array($this->getNodeTypeKey(), $allowedNodeTypes))
        {
            return;
        }

        throw new InvalidParentNodeTypeException('Parent does not allow node type of name "' . $this->getNodeTypeName() . '" as child.');
    }

    /**
     * The node source extension relation
     *
     * @return HasMany
     */
    public function nodeSourceExtensions()
    {
        return $this->hasMany(source_model_name($this->getNodeTypeName(), true));
    }

    /**
     * The node type relation
     *
     * @return BelongsTo
     */
    public function nodeType()
    {
        return $this->belongsTo(
            config('hierarchy.nodetype_model', 'Nuclear\Hierarchy\NodeType')
        );
    }

    /**
     * Getter for node type
     *
     * @return NodeType
     */
    public function getNodeType()
    {
        $bag = hierarchy_bag('nodetype');

        if ($this->relationLoaded('nodeType'))
        {
            $nodeType = $this->getRelation('nodeType');
        } elseif ($nodeType = $bag->getNodeType($this->getNodeTypeKey()))
        {
            $this->setRelation('nodeType', $nodeType);
        } else
        {
            $nodeType = $this->load('nodeType')->getRelation('nodeType');
        }

        if ($nodeType)
        {
            $bag->addNodeType($nodeType);

            return $nodeType;
        }

        return null;
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
     * Gets the node type name
     *
     * @return int $id
     */
    public function getNodeTypeName()
    {
        return $this->nodeTypeName ?:
            (is_null($this->getNodeType()) ? null : $this->getNodeType()->getName());
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
        $nodeType = NodeType::findOrFail($id);

        $this->nodeType()->associate($nodeType);

        $this->mailing = $nodeType->isTypeMailing();
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

        // When there is no node type we exclude source attributes
        return $this->_isTranslationAttribute($key) ||
        (is_null($this->getNodeTypeName()) ? false : $this->isSourceAttribute($key));
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
            'translationForeignKey',
            'nodeTypeName'
        ]);
    }

    /**
     * Checks if a key is a node source attribute
     *
     * @param $key
     * @return bool
     */
    protected function isSourceAttribute($key)
    {
        $modelName = source_model_name($this->getNodeTypeName(), true);

        return in_array($key, call_user_func([$modelName, 'getSourceFields']));
    }

    /**
     * Checks if the translation is dirty
     *
     * @param Eloquent $translation
     * @return bool
     */
    protected function isTranslationDirty(Eloquent $translation)
    {
        return $translation->isDirty();
    }

    /**
     * Determine if the given attribute may be mass assigned.
     * (This method is an extension to the base Model isFillable method.
     * It includes the node source attributes in order to check if keys are fillable.)
     *
     * @param  string $key
     * @return bool
     */
    public function isFillable($key)
    {
        // We can assume source attributes are fillable
        return $this->isSourceAttribute($key) || parent::isFillable($key);
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
            $this->getNodeTypeName()
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
            $translation = $this->translate($locale);

            $attribute = ($translation) ? $translation->{$key} : null;

            if (empty($attribute) && $fallback)
            {
                $translation = $this->translate($this->getFallbackLocale());

                if ($translation)
                {
                    return $translation->{$key};
                }
            }

            return $attribute;
        }

        return null;
    }

    /**
     * Get source or fallback to first found translation
     *
     * @param string|null $locale
     * @return NodeSource
     */
    public function translateOrFirst($locale = null)
    {
        $translation = $this->translate($locale, true);

        if ( ! $translation)
        {
            $translation = $this->translations->first();
        }

        return $translation;
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
            return $this->orderQueryBySourceAttribute($query, $key, $direction);
        }

        return $query->orderBy($key, $direction);
    }

    /**
     * Sorts by source attribute
     *
     * @param Builder $query
     * @param string $attribute
     * @param string $direction
     * @return Builder
     */
    public function scopeSortedBySourceAttribute(Builder $query, $attribute, $direction = 'ASC')
    {
        return $this->orderQueryBySourceAttribute($query, $attribute, $direction);
    }

    /**
     * @param Builder $query
     * @param $attribute
     * @param $direction
     * @return mixed
     */
    protected function orderQueryBySourceAttribute(Builder $query, $attribute, $direction)
    {
        $table = $this->_isTranslationAttribute($attribute) ?
            $this->sourcesTable :
            source_table_name($this->getNodeTypeName());

        $key = $this->getTable() . '.' . $this->getKeyName();

        return $query->join($table . ' as t', 't.node_id', '=', $key)
            ->select('t.id as source_id', 'nodes.*')
            ->groupBy($key)
            ->orderBy('t.' . $attribute, $direction);
    }

    /**
     * Scope for selecting with name
     *
     * @param Builder $query
     * @param string $name
     * @param string|null $locale
     * @return Builder
     */
    public function scopeWithName(Builder $query, $name, $locale = null)
    {
        return $this->scopeWhereTranslation($query, 'node_name', $name, $locale);
    }

    /**
     * Scope for selecting with type
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeWithType(Builder $query, $type)
    {
        // We do this for searching and sorting with source attributes
        $this->nodeTypeName = $type;

        return $this->scopeWhereTranslation($query, 'source_type', $type, null);
    }

    /**
     * Status filter scope
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeFilteredByStatus(Builder $query, $status = null)
    {
        $status = is_null($status) ? request('f', 'all') : $status;

        if (in_array($status, ['published', 'withheld', 'draft', 'pending', 'archived', 'invisible', 'locked']))
        {
            $query->{$status}();
        }

        return $query;
    }


    /**
     * Published scope
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query)
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
     * Withheld scope
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithheld(Builder $query)
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
     * @return Builder
     */
    public function scopeDraft(Builder $query)
    {
        return $query->where('status', Node::DRAFT);
    }

    /**
     * Pending scope
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', Node::PENDING)
            ->where('published_at', '>', Carbon::now());
    }

    /**
     * Archived scope
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeArchived(Builder $query)
    {
        return $query->where('status', Node::ARCHIVED);
    }


    /**
     * Scope invisible
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInvisible(Builder $query)
    {
        return $query->whereVisible(0);
    }

    /**
     * Scope locked
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLocked(Builder $query)
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
     * @return Collection|LengthAwarePaginator
     */
    public function getOrderedChildren($perPage = null)
    {
        $children = $this->children();

        $this->determineChildrenSorting($children);

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Returns all published children with parameter ordered
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPublishedOrderedChildren($perPage = null)
    {
        $children = $this->children()
            ->published();

        $this->determineChildrenSorting($children);

        return $this->determineChildrenPagination($perPage, $children);
    }

    /**
     * Determines the children sorting
     *
     * @param HasMany $children
     */
    public function determineChildrenSorting(HasMany $children)
    {
        if (in_array($this->children_order, $this->translatedAttributes))
        {
            $children->sortedBySourceAttribute(
                $this->children_order,
                $this->children_order_direction
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
     * @param mixed $perPage
     * @param HasMany $children
     * @return mixed
     */
    public function determineChildrenPagination($perPage, HasMany $children)
    {
        if ($perPage === false)
        {
            return $children;
        }

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
    public function hasTranslatedChildren($locale = null)
    {
        foreach($this->getChildren() as $child)
        {
            if($child->hasTranslation($locale))
            {
                return true;
            }
        }

        return false;
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
     * Sets the node status to locked
     *
     * @return $this
     */
    public function lock()
    {
        $this->setAttribute('locked', 1);

        return $this;
    }

    /**
     * Sets the node status to unlocked
     *
     * @return $this
     */
    public function unlock()
    {
        $this->setAttribute('locked', 0);

        return $this;
    }

    /**
     * Sets the node status to hidden
     *
     * @return $this
     */
    public function hide()
    {
        $this->setAttribute('visible', 0);

        return $this;
    }

    /**
     * Sets the node status to visible
     *
     * @return $this
     */
    public function show()
    {
        $this->setAttribute('visible', 1);

        return $this;
    }

    /**
     * Checks if node hides children
     *
     * @return bool
     */
    public function hidesChildren()
    {
        return $this->hides_children || $this->getNodeType()->hides_children;
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
     * Checks if a node is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return (bool)$this->locked;
    }

    /**
     * Checks if a node is locked
     *
     * @return bool
     */
    public function isVisible()
    {
        return (bool)$this->getAttribute('visible');
    }

    /**
     * Checks if the node is a mailing node
     *
     * @return bool
     */
    public function isMailing()
    {
        return (bool)$this->getAttribute('mailing');
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
            $translation->source_type = $nodeType->getName();
        }
    }

    /**
     * Remakes sources
     */
    protected function remakeSources(NodeType $nodeType)
    {
        foreach ($this->translations as $translation)
        {
            $source = $translation->getNewSourceModel($nodeType->getName());
            $source->id = $translation->getKey();

            $translation->relations['source'] = $source;
        }

        $this->propagateIdToSources();
    }

    /**
     * Most visited scope
     *
     * @param Builder $query
     * @param int|null $limit
     * @return Builder
     */
    public function scopeMostVisited(Builder $query, $limit = null)
    {
        $query->select(\DB::raw('nodes.*, count(*) as `aggregate`'))
            ->join('node_site_view', 'nodes.id', '=', 'node_site_view.node_id')
            ->groupBy('nodes.id')
            ->orderBy('aggregate', 'desc');

        if ($limit)
        {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Recently visited scope
     *
     * @param Builder $query
     * @param int|null $limit
     * @return Builder
     */
    public function scopeRecentlyVisited(Builder $query, $limit = null)
    {
        $query
            ->select(\DB::raw('nodes.*, MAX(node_site_view.site_view_id) as `aggregate`'))
            ->join('node_site_view', 'nodes.id', '=', 'node_site_view.node_id')
            ->orderBy('aggregate', 'desc')
            ->groupBy('node_site_view.node_id');

        if ($limit)
        {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Recently edited scope
     *
     * @param Builder $query
     * @param int|null $limit
     * @return Builder
     */
    public function scopeRecentlyEdited(Builder $query, $limit = null)
    {
        $query->orderBy('updated_at', 'desc');

        if ($limit)
        {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Recently created scope
     *
     * @param Builder $query
     * @param int|null $limit
     * @return Builder
     */
    public function scopeRecentlyCreated(Builder $query, $limit = null)
    {
        $query->orderBy('created_at', 'desc');

        if ($limit)
        {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Scopes the model for regular and mailing nodes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTypeMailing(Builder $query)
    {
        return $query->where('mailing', 0);
    }

    /**
     * Gets the full url for node
     *
     * @param string $locale
     * @return string
     */
    public function getNodeUrl($locale = null)
    {
        $node = $this;
        $uri = '';

        do
        {
            $uri = '/' . $node->getTranslationAttribute('node_name', $locale) . $uri;
            $node = $node->parent;
        } while ( ! is_null($node));

        return url($uri);
    }

    /**
     * Determines the default edit link for node
     *
     * @param null|string $locale
     * @return string
     */
    public function getDefaultEditUrl($locale = null)
    {
        $parameters = [
            $this->getKey(),
            $this->translateOrFirst($locale)->getKey()
        ];

        if ($this->hidesChildren())
        {
            if ($this->children_display_mode === 'tree')
            {
                $parameters = current($parameters);
            }

            return route('reactor.nodes.children.' . $this->children_display_mode,
                $parameters);
        }

        return route('reactor.nodes.edit',
            $parameters);
    }

    /**
     * It returns the searchable
     *
     * @return array
     */
    public function getSearchable()
    {
        // When there is no node type we exclude source attributes
        if (is_null($this->getNodeTypeName()))
        {
            return $this->searchable;
        }

        $modelName = source_model_name($this->getNodeTypeName(), true);

        return array_merge_recursive(
            $this->searchable,
            call_user_func([$modelName, 'getSearchable'])
        );
    }

    /**
     * Checks if the node can have more translations
     *
     * @return bool
     */
    public function canHaveMoreTranslations()
    {
        return (locale_count() > count($this->translations));
    }

    /**
     * Returns the node title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->translateOrFirst()->title;
    }

    /**
     * Checks if the node is taggable
     *
     * @return bool
     */
    public function isTaggable()
    {
        return $this->getNodeType()->isTaggable();
    }

}