<?php

namespace Nuclear\Hierarchy\Contract;

use Illuminate\Database\Eloquent\Model as Eloquent;

interface NodeTypeContract {

    /**
     * Returns the name of the node type
     *
     * @return string
     */
    public function getName();

    /**
     * Returns collections ordered by position
     *
     * @return Collection
     */
    public function getFields();

    /**
     * Add a field to the node type
     *
     * @param array $attributes
     * @return Eloquent
     */
    public function addField(array $attributes);

    /**
     * Returns keys of the associated fields
     *
     * @return array
     */
    public function getFieldKeys();

}