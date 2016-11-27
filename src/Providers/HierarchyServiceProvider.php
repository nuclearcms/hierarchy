<?php

namespace Nuclear\Hierarchy\Providers;


use Illuminate\Support\ServiceProvider;
use Nuclear\Hierarchy\Bags\NodeTypeBag;
use Nuclear\Hierarchy\Cache\Accessor;

class HierarchyServiceProvider extends ServiceProvider {

    const version = '2.3.7';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSourcePath();

        $this->registerSupporters();

        $this->registerNodeTypeBag();
    }

    /**
     * Register the generated sources path
     *
     * @return void
     */
    protected function registerSourcePath()
    {
        $this->app['path.generated'] = base_path(config('hierarchy.gen_path', 'gen'));
    }

    /**
     * Registers the NodeTypeBag
     *
     * @return void
     */
    protected function registerNodeTypeBag()
    {
        $this->app['hierarchy.bags.nodetype'] = $this->app->share(function ()
        {
            return new NodeTypeBag;
        });
    }

    /**
     * Registers support classes
     *
     * @return void
     */
    protected function registerSupporters()
    {
        $this->app->singleton(
            'hierarchy.support.locale',
            'Nuclear\Hierarchy\Support\LocaleManager'
        );

        $this->app->singleton(
            'hierarchy.nodebag',
            'Nuclear\Hierarchy\NodeBag'
        );
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // This is for model and migration templates
        // we use blade engine to generate these files
        $this->loadViewsFrom(dirname(__DIR__) . '/Support/templates', '_hierarchy');

        if ( ! $this->app->environment('production'))
        {
            $this->publishes([
                dirname(__DIR__) . '/Support/config.php' => config_path('hierarchy.php')
            ]);

            $this->publishes([
                dirname(__DIR__) . '/Support/migrations/' => database_path('/migrations')
            ], 'migrations');
        }
    }

}