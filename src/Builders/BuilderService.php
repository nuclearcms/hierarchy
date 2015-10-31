<?php

namespace Nuclear\Hierarchy\Builders;


use Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract;
use Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract;
use Nuclear\Hierarchy\Contract\Migration\MigratorContract;
use Nuclear\Hierarchy\Contract\NodeTypeContract;

class BuilderService implements BuilderServiceContract {

    /**
     * Builders for the service
     *
     * @var ModelBuilderContract
     * @var MigrationBuilderContract
     * @var CacheBuilderContract
     */
    protected $modelBuilder;
    protected $migrationBuilder;
    protected $cacheBuilder;

    /**
     * Constructor
     *
     * @param ModelBuilderContract $modelBuilder
     * @param MigrationBuilderContract $migrationBuilder
     * @param CacheBuilderContract $cacheBuilder
     */
    public function __construct(
        ModelBuilderContract $modelBuilder,
        MigrationBuilderContract $migrationBuilder,
        CacheBuilderContract $cacheBuilder
    )
    {
        $this->modelBuilder = $modelBuilder;
        $this->migrationBuilder = $migrationBuilder;
        $this->cacheBuilder = $cacheBuilder;
    }

    /**
     * Builds a source table and associated entities
     *
     * @param string $name
     * @param int $id
     */
    public function buildTable($name, $id)
    {
        $this->modelBuilder->build($name, []);
        $this->cacheBuilder->build($id, []);
        $migration = $this->migrationBuilder->buildSourceTableMigration($name);

        $this->migrateUp($migration);
    }

    /**
     * Builds a field on a source table and associated entities
     *
     * @param string $name
     * @param string $type
     * @param string $tableName
     * @param array $fields
     * @param NodeTypeContract $nodeType
     */
    public function buildField($name, $type, $tableName, array $fields, NodeTypeContract $nodeType)
    {
        $this->modelBuilder->build($tableName, $fields);
        $this->cacheBuilder->build($nodeType->getKey(), $fields);
        $migration = $this->migrationBuilder->buildFieldMigrationForTable($name, $type, $tableName);

        $this->migrateUp($migration);
    }

    /**
     * Destroys a source table and all associated entities
     *
     * @param string $name
     * @param array $fields
     * @param int $id
     */
    public function destroyTable($name, array $fields, $id)
    {
        $this->modelBuilder->destroy($name);
        $this->cacheBuilder->destroy($id);

        $migration = $this->migrationBuilder
            ->getMigrationClassPathByKey($name);

        $this->migrateDown($migration);

        $this->migrationBuilder->destroySourceTableMigration($name, $fields);
    }

    /**
     * Destroys a field on a source table and all associated entities
     *
     * @param string $name
     * @param string $tableName
     * @param array $fields
     * @param NodeTypeContract $nodeType
     */
    public function destroyField($name, $tableName, array $fields, NodeTypeContract $nodeType)
    {
        $this->modelBuilder->build($tableName, $fields);
        $this->cacheBuilder->build($nodeType->getKey(), $fields);

        $migration = $this->migrationBuilder
            ->getMigrationClassPathByKey($tableName, $name);

        $this->migrateDown($migration);

        $this->migrationBuilder->destroyFieldMigrationForTable($name, $tableName);
    }

    /**
     * Migrates a migration
     *
     * @param string $class
     */
    protected function migrateUp($class)
    {
        $this->resolveMigration($class)->up();
    }

    /**
     * Reverses a migration
     *
     * @param string $class
     */
    protected function migrateDown($class)
    {
        $this->resolveMigration($class)->down();
    }

    /**
     * Creates a migration class
     *
     * @param string $class
     * @return MigrationContract
     * @throws \RuntimeException
     */
    protected function resolveMigration($class)
    {
        if (class_exists($class))
        {
            return new $class;
        }

        throw new \RuntimeException('Class ' . $class . ' does not exist.');
    }

}