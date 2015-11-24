<?php

namespace Nuclear\Hierarchy\Builders;


use Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract;
use Nuclear\Hierarchy\Contract\Builders\WriterContract;

class MigrationBuilder implements MigrationBuilderContract, WriterContract {

    use Writer;

    /**
     * Patterns for migration file names
     *
     * @var string
     */
    const PATTERN_TABLE = 'HierarchyCreate%sSourceTable';
    const PATTERN_FIELD = 'HierarchyCreate%sFieldFor%sSourceTable';

    /**
     * Prefix for tables
     *
     * @var string
     */
    const TABLE_PREFIX = 'ns_';

    /**
     * The migration class path
     *
     * @var string
     */
    const MIGRATION_PATH = 'gen\\Migrations';

    /**
     * Type map
     *
     * @var array
     */
    protected $typeMap;

    /**
     * Default type
     *
     * @var array
     */
    protected $defaultType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->typeMap = config('hierarchy.type_map', [
            'text'     => 'string',
            'textarea' => 'text',
            'markdown' => 'longtext',
            'document'     => 'unsignedInteger',
            'gallery'  => 'text',
            'checkbox' => 'boolean',
            'select'   => 'string',
            'number'   => 'double',
            'color'    => 'string',
            'slug'     => 'string',
            'tag'      => 'text',
            'password' => 'string',
            'datetime' => 'timestamp'
        ]);

        $this->defaultType = config('hierarchy.default_type', 'string');
    }

    /**
     * Builds a source table migration
     *
     * @param string $name
     * @return string
     */
    public function buildSourceTableMigration($name)
    {
        $path = $this->getMigrationPath($name);
        $migration = $this->getTableMigrationName($name);

        $contents = view('_hierarchy::migrations.table', [
            'table'     => str_plural(MigrationBuilder::TABLE_PREFIX . $name),
            'migration' => $migration
        ])->render();

        $this->write($path, $contents);

        return $this->getMigrationClassPath($migration);
    }

    /**
     * Destroy a source table migration
     *
     * @param string $name
     * @param array $fields
     */
    public function destroySourceTableMigration($name, array $fields)
    {
        $path = $this->getMigrationPath($name);

        $this->delete($path);

        foreach ($fields as $field)
        {
            $this->destroyFieldMigrationForTable($field, $name);
        }
    }

    /**
     * Builds a field migration for a table
     *
     * @param string $name
     * @param string $type
     * @param string $tableName
     * @return string
     */
    public function buildFieldMigrationForTable($name, $type, $tableName)
    {
        $path = $this->getMigrationPath($tableName, $name);
        $migration = $this->getTableFieldMigrationName($name, $tableName);

        $contents = view('_hierarchy::migrations.field', [
            'field'     => $name,
            'table'     => str_plural(MigrationBuilder::TABLE_PREFIX . $tableName),
            'migration' => $migration,
            'type'      => $this->getColumnType($type)
        ])->render();

        $this->write($path, $contents);

        return $this->getMigrationClassPath($migration);
    }

    /**
     * Destroys a field migration for a table
     *
     * @param string $name
     * @param string $tableName
     */
    public function destroyFieldMigrationForTable($name, $tableName)
    {
        $path = $this->getMigrationPath($tableName, $name);

        $this->delete($path);
    }

    /**
     * Returns the migration name for table
     *
     * @param string $name
     * @return string
     */
    public function getTableMigrationName($name)
    {
        return sprintf(
            MigrationBuilder::PATTERN_TABLE,
            ucfirst($name)
        );
    }

    /**
     * Returns the migration name for a field in table
     *
     * @param string $name
     * @param string $tableName
     * @return string
     */
    public function getTableFieldMigrationName($name, $tableName)
    {
        return sprintf(
            MigrationBuilder::PATTERN_FIELD,
            ucfirst($name),
            ucfirst($tableName)
        );
    }

    /**
     * Getter for entity base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return generated_path('Migrations');
    }

    /**
     * Returns the path for the migration
     *
     * @param string $table
     * @param string|null $field
     * @return string
     */
    public function getMigrationPath($table, $field = null)
    {
        $name = is_null($field) ?
            $this->getTableMigrationName($table) :
            $this->getTableFieldMigrationName($field, $table);

        return sprintf(
            $this->getBasePath() . '/%s.php',
            $name);
    }

    /**
     * Getter for migration class path
     *
     * @param string|null $migration
     * @return string
     */
    public function getMigrationClassPath($migration = '')
    {
        return MigrationBuilder::MIGRATION_PATH . ($migration ? '\\' . $migration : '');
    }

    /**
     * Returns the migration class path by key
     *
     * @param string $table
     * @param string|null $field
     * @return string
     */
    public function getMigrationClassPathByKey($table, $field = null)
    {
        $name = is_null($field) ?
            $this->getTableMigrationName($table) :
            $this->getTableFieldMigrationName($field, $table);

        return $this->getMigrationClassPath($name);
    }

    /**
     * Returns the column type for key
     *
     * @param string $type
     * @return string
     */
    public function getColumnType($type)
    {
        if (array_key_exists($type, $this->typeMap))
        {
            return $this->typeMap[$type];
        }

        return $this->defaultType;
    }

}