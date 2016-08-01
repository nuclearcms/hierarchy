<?php

namespace Nuclear\Hierarchy\Contract;


interface NodeFieldContract {

    /**
     * Returns the name of the node field
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the type of the node field
     *
     * @return string
     */
    public function getType();

    /**
     * Checks if the node field is indexed
     *
     * @return bool
     */
    public function isIndexed();

}