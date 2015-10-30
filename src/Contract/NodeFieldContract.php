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

}