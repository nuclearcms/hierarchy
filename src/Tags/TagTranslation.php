<?php

namespace Reactor\Tags;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TagTranslation extends Model {

    /**
     * The fillable fields for the model.
     *
     * @var  array
     */
    protected $fillable = ['title', 'tag_name'];

    public $timestamps = false;

    /**
     * Boot the model
     */
    public static function boot()
    {
        TagTranslation::saving(function ($translation)
        {
            $translation->setSlugFromName();
        });
    }

    /**
     * Sets the tag slug
     *
     * @param string
     * @return void
     */
    public function setSlugFromName()
    {
        if (empty($this->tag_name))
        {
            $this->setAttribute('tag_name',
                str_slug($this->getAttribute('title')));
        }
    }

    /**
     * Returns the tag translation by tag name
     *
     * @param string $name
     * @return TagTranslation
     */
    public static function findByName($name)
    {
        return static::whereTagName($name)->first();
    }

    /**
     * Tag relation
     *
     * @return BelongsTo
     */
    public function tag()
    {
        return $this->belongsTo('Reactor\Tags\Tag');
    }

    /**
     * Node relation
     *
     * @return BelongsToMany
     */
    public function nodes()
    {
        return $this->tag->nodes();
    }

}
