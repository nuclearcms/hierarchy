<?php

namespace Nuclear\Hierarchy\Contract\Builders;


interface BuilderServiceContract {

    /**
     * Builds a source table and associated entities
     *
     * @param string $name
     */
    public function buildTable($name);

    /**
     * Builds a field on a source table and associated entities
     *
     * @param string $name
     * @param string $type
     * @param string $tableName
     * @param array $fields
     */
    public function buildField($name, $type, $tableName, array $fields);

    /**
     * Destroys a source table and all associated entities
     *
     * @param string $name
     * @param array $fields
     */
    public function destroyTable($name, array $fields);

    /**
     * Destroys a field on a source table and all associated entities
     *
     * @param string $name
     * @param string $tableName
     * @param array $fields
     */
    public function destroyField($name, $tableName, array $fields);

}