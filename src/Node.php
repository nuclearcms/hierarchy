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
     * The translation model is the NodeSource for us
     *
     * @var string
     */
    protected $translationModel = 'Nuclear\Hierarchy\NodeSource';

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
     * Published scope
     *
     * @param Builder $query
     */
    public function scopePublished($query)
    {
        return $query
            ->where('status' >= Node::PUBLISHED)
            ->orWhere(function ($query)
            {
                $query->where('status' >= Node::PENDING)
                    ->where('published_at', '<=', Carbon::now());
            });
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
     * @param int|null $page
     * @return Collection
     */
    public function getOrderedChildren($perPage = null, $page = 1)
    {
        $children = $this->getChildren()->sortBy(
            $this->children_order, SORT_REGULAR,
            ($this->children_order_direction === 'asc') ? true : false
        );

        return $this->determinePagination($perPage, $page, $children);
    }

    /**
     * Returns all children ordered by position
     *
     * @param int|null $perPage
     * @param int|null $page
     * @return Collection
     */
    public function getPositionOrderedChildren($perPage = null, $page = 1)
    {
        $children = $this->getChildren()
            ->sortBy($this->getLftName());

        return $this->determinePagination($perPage, $page, $children);
    }

    /**
     * Creates a paginator instance if needed
     *
     * @param $perPage
     * @param $page
     * @param $children
     * @return LengthAwarePaginator
     */
    protected function determinePagination($perPage, $page, $children)
    {
        if (is_null($perPage))
        {
            return $children;
        }

        return new LengthAwarePaginator(
            $children->forPage($page, $perPage),
            count($children), $perPage, $page);
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
     * Checks if node hides children
     *
     * @return bool
     */
    public function hidesChildren()
    {
        return $this->hides_children || $this->nodeType->hides_children;
    }

}