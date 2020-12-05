<?php

namespace Nuclear\Hierarchy;

class SiteContent extends Content {

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

}