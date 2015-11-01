<?php

use Orchestra\Testbench\TestCase;
use org\bovigo\vfs\vfsStream;

class TestBase extends TestCase {

    protected $root;

    public function setUp()
    {
        parent::setUp();

        $this->resetDatabase();

        $this->setBasePath();

        $this->registerAutoloader();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/..';

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => ''
        ]);
    }

    protected function getPackageProviders($app)
    {
        return ['Nuclear\Hierarchy\Providers\HierarchyServiceProvider'];
    }

    protected function setBasePath()
    {
        $this->root = vfsStream::setup('gen');

        $this->app['path.generated'] = vfsStream::url('gen');
    }

    protected function resetDatabase()
    {
        // Relative to the testbench app folder: vendors/orchestra/testbench/src/fixture
        $migrationsPath = 'src/Support/migrations';
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        // Migrate
        $artisan->call('migrate', [
            '--database' => 'sqlite',
            '--path'     => $migrationsPath,
        ]);
    }

    /**
     * Registers the generated entities autoloader
     */
    protected function registerAutoloader()
    {
        spl_autoload_register(function ($class)
        {
            $prefix = 'gen\\';
            $base_dir = vfsStream::url('gen/');

            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0)
            {
                // no, move to the next registered autoloader
                return;
            }

            // get the relative class name
            $relative_class = substr($class, $len);

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $base_dir .str_replace('\\', '/', $relative_class) . '.php';

            // if the file exists, require it
            if (file_exists($file))
            {
                require $file;
            }
        });
    }
}