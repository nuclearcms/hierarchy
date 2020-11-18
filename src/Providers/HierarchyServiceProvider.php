<?php

namespace Nuclear\Hierarchy\Providers;

use Illuminate\Support\ServiceProvider;

class HierarchyServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register any tags services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../resources/lang' => resource_path('lang/vendor/hierarchy')], 'lang');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'hierarchy');

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');

        require __DIR__ . '/../Support/helpers.php';
    }

}
