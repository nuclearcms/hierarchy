<?php

namespace Nuclear\Hierarchy\Bags;


use Illuminate\Support\Collection;
use Nuclear\Hierarchy\Contract\Bags\NodeTypeBagContract;
use Nuclear\Hierarchy\Contract\NodeTypeContract;

class NodeTypeBag extends Collection implements NodeTypeBagContract {

    /**
     * Adds a node type to the bag
     *
     * @param NodeTypeContract $nodeType
     */
    public function addNodeType(NodeTypeContract $nodeType)
    {
        if ( ! $this->hasNodeType($nodeType->getKey()))
        {
            $this->put($nodeType->getKey(), $nodeType);
        }
    }

    /**
     * Gets a node type from the bag
     *
     * @param int $id
     * @return NodeTypeContract
     */
    public function getNodeType($id)
    {
        return $this->get($id);
    }

    /**
     * Checks if has node type
     *
     * @param int $id
     * @return bool
     */
    public function hasNodeType($id)
    {
        return $this->has($id);
    }
}