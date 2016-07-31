<?php

namespace Nuclear\Hierarchy\Contract\Builders;


use Nuclear\Hierarchy\Contract\NodeTypeContract;

interface BuilderServiceContract {

    /**
     * Builds a source table and associated entities
     *
     * @param string $name
     * @param int $id
     */
    public function buildTable($name, $id);

    /**
     * Builds a field on a source table and associated entities
     *
     * @param string $name
     * @param string $type
     * @param string $tableName
     * @param NodeTypeContract $nodeType
     */
    public function buildField($name, $type, $tableName, NodeTypeContract $nodeType);

    /**
     * Destroys a source table and all associated entities
     *
     * @param string $name
     * @param array $fields
     * @param int $id
     */
    public function destroyTable($name, array $fields, $id);

    /**
     * Destroys a field on a source table and all associated entities
     *
     * @param string $name
     * @param string $tableName
     * @param NodeTypeContract $nodeType
     */
    public function destroyField($name, $tableName, NodeTypeContract $nodeType);

}