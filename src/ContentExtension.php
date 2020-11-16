<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContentExtension extends Model {

	use HasTranslations;

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'value'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['value' => 'array'];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = ['value'];

	/**
     * Content relation
     *
     * @return BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

}