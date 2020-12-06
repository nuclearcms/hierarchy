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
    protected $fillable = ['field_id', 'name', 'type', 'value'];

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

	/**
     * Content relation
     *
     * @return BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Loads media for the extension
     *
     * @return self
     */
    public function loadMedia()
    {
        if(is_null($translations = $this->getTranslations('value'))) return $this;

        foreach($translations as $locale => $translation)
        {
            if(empty($translation)) continue;

            if($this->type == 'MediaField') {
                $this->setTranslation('value', $locale, is_array($translation) ? get_media($translation) : get_medium($translation));
            } elseif($this->type == 'TextEditorField') {
                $this->setTranslation('value', $locale, $this->loadEditorMedia($translation));
            }
            
        }

        return $this;
    }

    /**
     * Loads media for text editor fields
     *
     * @param array $translation
     * @return array
     */
    public function loadEditorMedia($content)
    {
        foreach($content['blocks'] as &$block) {
            if($block['type'] == 'media') {
                if(!empty($block['data']['media'])) $block['data']['media'] = get_media($block['data']['media']);
            }
        }

        return $content;
    }

    /**
     * Loads relations for the extension
     *
     * @return self
     */
    public function loadRelations()
    {
        if(is_null($translations = $this->getTranslations('value'))) return $this;

        foreach($translations as $locale => $translation)
        {
            if(empty($translation)) continue;

            $this->setTranslation('value', $locale, is_array($translation)
                ? Content::whereIn('id', $translation)->orderByRaw('FIELD (id, ' . implode(', ', $translation) . ') ASC')->get()
                : Content::find($translation));            
        }

        return $this;
    }

}