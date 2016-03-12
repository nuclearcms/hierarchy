<?php

namespace Nuclear\Hierarchy\Providers;


use Illuminate\Support\ServiceProvider;
use Nuclear\Hierarchy\Cache\Accessor;

class HierarchyServiceProvider extends ServiceProvider {

    const version = '1.2.12';

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

        $this->registerCacheAccessor();

        $this->registerExternalServices();

        $this->registerBuilders();
    }

    /**
     * Registers builders
     *
     * @return void
     */
    protected function registerBuilders()
    {
        $this->registerModelBuilder();
        $this->registerMigrationBuilder();
        $this->registerFormBuilder();
        $this->registerCacheBuilder();
        $this->registerBuilderService();
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
     * Registers the cache accessor
     *
     * @return void
     */
    protected function registerCacheAccessor()
    {
        $this->app['hierarchy.cache'] = $this->app->share(function ()
        {
            return new Accessor;
        });
    }

    /**
     * Registers external services needed for the package
     *
     * @return void
     */
    protected function registerExternalServices()
    {
        $this->app->register('Dimsav\Translatable\TranslatableServiceProvider');
    }

    /**
     * Registers the model builder
     *
     * @return void
     */
    protected function registerModelBuilder()
    {
        $this->app->bind(
            'Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract',
            'Nuclear\Hierarchy\Builders\ModelBuilder'
        );
    }

    /**
     * Registers the migration builder
     *
     * @return void
     */
    protected function registerMigrationBuilder()
    {
        $this->app->bind(
            'Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract',
            'Nuclear\Hierarchy\Builders\MigrationBuilder'
        );
    }

    /**
     * Registers the form builder
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->bind(
            'Nuclear\Hierarchy\Contract\Builders\FormBuilderContract',
            'Nuclear\Hierarchy\Builders\FormBuilder'
        );
    }

    /**
     * Registers the migration builder
     *
     * @return void
     */
    protected function registerCacheBuilder()
    {
        $this->app->bind(
            'Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract',
            'Nuclear\Hierarchy\Builders\CacheBuilder'
        );
    }

    /**
     * Registers the builder service
     *
     * @return void
     */
    protected function registerBuilderService()
    {
        $this->app->bind(
            'Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract',
            'Nuclear\Hierarchy\Builders\BuilderService'
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

        $this->publishes([
            dirname(__DIR__) . '/Support/config.php' => config_path('hierarchy.php')
        ]);

        $this->publishes([
            dirname(__DIR__) . '/Support/migrations/' => database_path('/migrations')
        ], 'migrations');
    }

}