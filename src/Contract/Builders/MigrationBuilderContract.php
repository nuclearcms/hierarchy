<?php

namespace Nuclear\Hierarchy\Contract\Builders;


interface MigrationBuilderContract {

    /**
     * Builds a source table migration
     *
     * @param string $name
     * @return string
     */
    public function buildSourceTableMigration($name);

    /**
     * Destroy a source table migration
     *
     * @param string $name
     * @param array $fields
     */
    public function destroySourceTableMigration($name, array $fields);

    /**
     * Builds a field migration for a table
     *
     * @param string $name
     * @param string $type
     * @param bool $indexed
     * @param string $tableName
     * @return string
     */
    public function buildFieldMigrationForTable($name, $type, $indexed, $tableName);

    /**
     * Destroys a field migration for a table
     *
     * @param string $name
     * @param string $tableName
     */
    public function destroyFieldMigrationForTable($name, $tableName);

    /**
     * Returns the migration name for table
     *
     * @param string $name
     * @return string
     */
    public function getTableMigrationName($name);

    /**
     * Returns the migration name for a field in table
     *
     * @param string $name
     * @param string $tableName
     * @return string
     */
    public function getTableFieldMigrationName($name, $tableName);

    /**
     * Returns the path for the migration
     *
     * @param string $table
     * @param string|null $field
     * @return string
     */
    public function getMigrationPath($table, $field = null);

    /**
     * Getter for migration class path
     *
     * @param string|null $migration
     * @return string
     */
    public function getMigrationClassPath($migration = null);

    /**
     * Returns the migration class path by key
     *
     * @param string $table
     * @param string|null $field
     * @return string
     */
    public function getMigrationClassPathByKey($table, $field = null);

    /**
     * Returns the column type for key
     *
     * @param string $type
     * @return string
     */
    public function getColumnType($type);

}