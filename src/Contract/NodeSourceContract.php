<?php

namespace Nuclear\Hierarchy\Contract;


interface NodeSourceContract {

    /**
     * Returns the source model name
     *
     * @param string|null $type
     * @return string
     */
    public function getSourceModelName($type = null);

    /**
     * Getter for source data
     *
     * @return mixed
     */
    public function getSource();

    /**
     * Getter for node name
     *
     * @return string
     */
    public function getTitle();

    /**
     * Getter for node name
     *
     * @return string
     */
    public function getNodeName();

    /**
     * Sets the node name
     *
     * @param string
     * @return void
     */
    public function setNodeName($name);

    /**
     * Set node name from title
     *
     * @return void
     */
    public function setNodeNameFromTitle();

}