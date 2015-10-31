<?php

namespace Nuclear\Hierarchy\Contract\Cache;


interface AccessorContract {

    /**
     * Reads the cache
     *
     * @return array
     */
    public function read();

    /**
     * Writes the cache
     *
     * @param array $data
     */
    public function write(array $data);

    /**
     * Getter for fields in node type
     *
     * @param int $id
     * @return array
     */
    public function getFieldsFor($id);

    /**
     * Checks if a node type has key
     *
     * @param int $id
     * @param string $field
     * @return bool
     */
    public function nodeTypeHasField($id, $field);



}