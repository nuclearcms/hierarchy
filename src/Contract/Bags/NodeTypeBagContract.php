<?php

namespace Nuclear\Hierarchy\Contract\Bags;


use Nuclear\Hierarchy\Contract\NodeTypeContract;

interface NodeTypeBagContract {

    /**
     * Adds a node type to the bag
     *
     * @param NodeTypeContract $nodeType
     */
    public function addNodeType(NodeTypeContract $nodeType);

    /**
     * Gets a node type from the bag
     *
     * @param int $id
     */
    public function getNodeType($id);

    /**
     * Checks if has node type
     *
     * @param int $id
     */
    public function hasNodeType($id);

}