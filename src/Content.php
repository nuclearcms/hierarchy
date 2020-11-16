<?php

namespace Nuclear\Hierarchy;

use Franzose\ClosureTable\Models\Entity;
use Spatie\Translatable\HasTranslations;

class Content extends Entity {

    use HasTranslations, HasSlug;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contents';

    /**
     * ClosureTable model instance.
     *
     * @var \Nuclear\Hierarchy\ContentClosure
     */
    protected $closure = ContentClosure::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'visible', 'sterile', 'locked', 'status', 'hides_children', 'priority',
        'published_at', 'children_display_mode', 'title', 'slug', 'keywords',
        'meta_title', 'meta_description', 'meta_author', 'meta_image'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'keywords' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'meta_author' => 'array',
        'meta_image' => 'array'
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'title', 'slug', 'keywords', 'meta_title',
        'meta_description', 'meta_author', 'meta_image'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * Content Type relation
     *
     * @return BelongsTo
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Content Extension relation
     *
     * @return HasMany
     */
    public function contentExtension()
    {
        return $this->hasMany(ContentExtension::class);
    }

}
