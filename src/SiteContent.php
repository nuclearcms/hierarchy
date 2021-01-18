<?php

namespace Nuclear\Hierarchy;

class SiteContent extends Content {

    /** @var array */
    protected $loadables = [];

    /** @var array */
    protected $loadableKeys = ['cover_image' => 'MediaField'];

    /**
     * Boot the model
     */
    public static function boot()
    {
        parent::boot();

        if(!is_request_reactor()) {
            static::addGlobalScope(new SiteViewableScope);
        }
    }

    /**
     * Resolve by slug
     *
     * @param mixed
     * @param string|null $field
     * @return SiteContent
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'slug->' . app()->getLocale(), $value)->first();
    }

    /**
     * Get a loadble
     *
     * @param string $key
     * @return mixed
     */
    protected function getLoadable($key)
    {
        $locale = $this->getLocale();

        if(isset($this->loadables[$key])) {
            if(isset($this->loadables[$key][$locale])) return $this->loadables[$key][$locale];
            if(isset($this->loadables[$key][config('app.fallback_locale')])) return $this->loadables[$key][config('app.fallback_locale')];
        }

        return null;
    }

    /**
     * Shortcut to content extension attributes
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if($this->isExtensionAttribute($key)) return $this->loadExtensionValue($key);

        if(array_key_exists($key, $this->loadableKeys)) return $this->loadSelfValue($key);

        return parent::getAttribute($key);
    }

    /**
     * Looks for or loads self value
     *
     * @param string $key
     * @return mixed
     */
    protected function loadSelfValue($key)
    {
        // Look for it in the cache first
        if($value = $this->getLoadable($key)) return $value;

        if(is_null($translations = $this->getTranslations($key))) {
            $this->loadables[$key] = null;
            return null;
        }

        $this->{'cache' . $this->loadableKeys[$key] . 'Loadable'}($translations, $key, null);

        return $this->getLoadable($key);
    }

    /**
     * Caches a media field in loadables
     *
     * @param array $translations
     * @param string $key
     * @param ContentExtension $extension
     */
    protected function cacheMediaFieldLoadable(array $translations, $key, $extension)
    {
        foreach($translations as $l => $translation) {
            $this->loadables[$key][$l] = empty($translation)
                ? null
                : (is_array($translation)
                    ? get_media($translation)
                    : get_medium($translation));
        }
    }

    /**
     * Caches a content relation field in loadables
     *
     * @param array $translations
     * @param string $key
     * @param ContentExtension $extension
     */
    protected function cacheContentRelationFieldLoadable(array $translations, $key, $extension)
    {
        foreach($translations as $l => $translation) {
            $this->loadables[$key][$l] = empty($translation)
                ? null
                : (is_array($translation)
                    ? SiteContent::whereIn('id', $translation)->orderByRaw('FIELD (id, ' . implode(', ', $translation) . ') ASC')->get()
                    : SiteContent::find($translation));
        }
    }

    /**
     * Caches a text editor field in loadables
     *
     * @param array $translations
     * @param string $key
     * @param ContentExtension $extension
     */
    protected function cacheTextEditorFieldLoadable(array $translations, $key, $extension)
    {
        foreach($translations as $l => $translation) {
                $this->loadables[$key][$l] = empty($translation)
                    ? null
                    : $extension->loadEditorMedia($translation);
            }
    }

    /** 
     * Looks for or loads extension data
     *
     * @param string $key
     * @return mixed
     */
    protected function loadExtensionValue($key)
    {
        $extension = $this->getExtension($key);

        // If it is not a loaded field return basic
        if(!in_array($extension->type, ['MediaField', 'TextEditorField', 'ContentRelationField'])) return $extension->value;

        // Look for it in the cache first
        if($value = $this->getLoadable($key)) return $value;

        if(is_null($translations = $extension->getTranslations('value'))) {
            $this->loadables[$key] = null;
            return null;
        }

        $this->{'cache' . $extension->type . 'Loadable'}($translations, $key, $extension);

        return $this->getLoadable($key);
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return 'Nuclear\Hierarchy\Content';
    }

}