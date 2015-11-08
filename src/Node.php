<?php

namespace Nuclear\Hierarchy;


use Baum\Node as BaumNode;
use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Node extends BaumNode {

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
        'visible', 'sterile', 'status', 'hides_children', 'priority',
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
     * Checks if key is a translation attribute
     *
     * @param string $key
     * @return bool
     */
    public function isTranslationAttribute($key)
    {
        if ($this->isNodeTypeKey($key))
        {
            return false;
        }

        return $this->_isTranslationAttribute($key) || $this->isCachedAttribute($key);
    }

    /**
     * Checks if the given key is a node type key
     * (This key requires special protection)
     *
     * @param $key
     * @return bool
     */
    protected function isNodeTypeKey($key)
    {
        return $key === $this->nodeTypeKey;
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
     * We are overloading Translatable's setAttribute method
     * until the pull PR is implemented.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (str_contains($key, ':')) {
            list($key, $locale) = explode(':', $key);
        } else {
            $locale = $this->locale();
        }

        if ($this->isTranslationAttribute($key)) {
            $this->getTranslationOrNew($locale)->$key = $value;
        } else {
            parent::setAttribute($key, $value);
        }
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
     * Overloading the Translatable __isset method
     * This will be removed when the PR is implemented
     *
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return ($this->isTranslationAttribute($key) || parent::__isset($key));
    }

    /**
     * A dirty work-through to be able to keep
     * Baum and Translatable play well
     * @link https://github.com/dimsav/laravel-translatable/issues/25
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        // We get the translations before they disappear
        // and we feed it to the overloaded saveTranslations method
        $translations = $this->translations;

        if ($this->exists) {
            if (count($this->getDirty()) > 0) {
                // If $this->exists and dirty, parent::save() has to return true. If not,
                // an error has occurred. Therefore we shouldn't save the translations.
                if (parent::save($options)) {
                    // Put the translations back so that they can be saved
                    return $this->saveTranslations($translations);
                }

                return false;
            } else {
                // If $this->exists and not dirty, parent::save() skips saving and returns
                // false. So we have to save the translations
                if ($saved = $this->saveTranslations($translations)) {
                    $this->fireModelEvent('saved', false);
                }

                return $saved;
            }
        } elseif (parent::save($options)) {
            // We save the translations only if the instance is saved in the database.
            return $this->saveTranslations($translations);
        }

        return false;
    }

    /**
     * Made a minor modification to the original translatable method
     * We accept translations as a parameter instead of calling $this->translations
     * in the foreach statement.
     *
     * @param Collection $translations
     * @return bool
     */
    protected function saveTranslations(Collection $translations)
    {
        $saved = true;
        foreach ($translations as $translation) {
            if ($saved && $this->isTranslationDirty($translation)) {
                $translation->setAttribute($this->getRelationKey(), $this->getKey());
                $saved = $translation->save();
            }
        }

        return $saved;
    }

    /**
     * Published scope
     *
     * @param Builder $query
     */
    public function scopePublished($query)
    {
        return $query->where(
            'published_at', '<=', Carbon::now()
        );
    }

    /**
     * Get ordered children
     *
     * @return Collection
     */
    public function getOrderedChildren()
    {
        return $this->children()
            ->orderBy(
                $this->children_order,
                $this->children_order_direction)
            ->get();
    }

    /**
     * Get ordered children paginated
     *
     * @return Collection
     */
    public function getOrderedChildrenPaginated()
    {
        return $this->children()
            ->orderBy(
                $this->children_order,
                $this->children_order_direction)
            ->paginate();
    }

}