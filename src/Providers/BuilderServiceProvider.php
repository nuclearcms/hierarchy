<?php

namespace Nuclear\Hierarchy\Providers;


use Illuminate\Support\ServiceProvider;

class BuilderServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract',
            'Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract',
            'Nuclear\Hierarchy\Contract\Builders\FormBuilderContract',
            'Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract',
            'Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract'
        ];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModelBuilder();
        $this->registerMigrationBuilder();
        $this->registerFormBuilder();
        $this->registerCacheBuilder();
        $this->registerBuilderService();
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
    
}