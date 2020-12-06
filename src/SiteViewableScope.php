<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SiteViewableScope implements Scope {

	/**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
    	// We check for the token, otherwise we publish the scope
    	if(app('Nuclear\Reactor\Support\TokenManager')->requestHasToken('preview_contents')) return $model;

        return $model->scopePublished($builder);
    }

}